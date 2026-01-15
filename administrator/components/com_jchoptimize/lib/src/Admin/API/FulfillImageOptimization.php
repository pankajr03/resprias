<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2025 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\API;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Response;
use _JchOptimizeVendor\V91\Psr\Http\Message\StreamInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Admin\Ajax\OptimizeImage;
use JchOptimize\Core\Platform\PathsInterface;

use function file_exists;
use function json_decode;
use function pathinfo;

class FulfillImageOptimization
{
    public function __construct(
        private MessageEventInterface $messageEvent,
        private LoggerInterface $logger,
        private AdminHelper $adminHelper,
        private PathsInterface $paths,
    ) {
    }

    public function handle(Response $response, array $index): void
    {
        $body = $response->getBody();
        $body->rewind();
        $contents = $body->getContents();
        $data = json_decode($contents, true);

        if (!isset($data)) {
            $this->messageEvent->send('Unknown response from server! Aborting...', 'apiError');
            $this->logger->error('Unknown response from server: ' . print_r($contents, true));

            return;
        }

        if (!$data['success']) {
            $this->messageEvent->send($data['message'] . '. Aborting...', 'apiError');

            return;
        }

        foreach ($data['data'] as $i => $fileEntry) {
            $originalImage = $index[$i];
            $this->processFileEntry($originalImage, $fileEntry);
        }
    }

    private function processFileEntry(string $originalImage, array $entry): void
    {
        $this->handleOptimized($originalImage, $entry['optimized']);

        foreach (['webp', 'avif'] as $resultType) {
            $this->handleConversions($originalImage, $resultType, result: $entry[$resultType] ?? []);
        }

        $this->handleResponsive($originalImage, responsiveResult: $entry['responsive'] ?? []);
    }

    private function handleOptimized(string $originalImage, array $optimizedResult): void
    {
        $message = $this->startMessage($originalImage);

        if (!isset($optimizedResult['success']) || !$optimizedResult['success']) {
            $message .= $optimizedResult['message'] ?? '';
            if (isset($optimizedResult['code']) && $optimizedResult['code'] == 304) {
                $this->adminHelper->markOptimized($originalImage);
                $this->messageEvent->send($message, 'alreadyOptimized');
            } else {
                $this->messageEvent->send($message, 'optimizationFailed');
            }
            return;
        }

        $backupFile = $this->getBackupFilename($originalImage);
        $fileMessage = ' optimized file.';

        if (!@file_exists($backupFile)) {
            $overwriteOriginal = $this->adminHelper->copyImage($originalImage, $backupFile);
            $fileMessage = ' backup file - ' . $backupFile;
        } else {
            $overwriteOriginal = true;
        }

        $imageData = $optimizedResult['data'];
        //Copy optimized file over original only if backup was successful
        if ($overwriteOriginal && $this->adminHelper->copyImage($imageData['url'], $originalImage)) {
            $message .= 'Optimized! You saved ' . $imageData['savings'] . ' bytes';

            $this->messageEvent->send($message, 'fileOptimized');
            $this->adminHelper->markOptimized($originalImage);
        } else {
            $message .= 'Could not copy' . $fileMessage;
            $this->messageEvent->send($message, 'optimizationFailed');
        }
    }

    private function handleConversions(string $originalImage, string $conversionType, array $result): void
    {
        if (empty($result)) {
            return;
        }

        $message = $this->startMessage($originalImage);

        if (!isset($result['success']) || !$result['success']) {
            $message .= $result['message'] ?? 'Conversion to ' . strtoupper($conversionType) . ' failed.';
            $this->messageEvent->send($message);

            return;
        }

        $imageData = $result['data'];
        if ($this->processImage($this->paths->nextGenImagesPath(), $imageData['url'], $originalImage)) {
            $message .= 'Converted to ' . strtoupper($conversionType) . '!'
            . ' You saved ' . $imageData['savings'] . ' bytes.';
            $this->messageEvent->send($message, 'webpGenerated');
        } else {
            $this->messageEvent->send('Could not save ' . strtoupper($conversionType) . '.', 'optimizationFailed');
        }
    }

    private function handleResponsive(string $originalImage, array $responsiveResult): void
    {
        $success = 0;

        foreach ($responsiveResult as $breakpoint => $resultTypes) {
            foreach ($resultTypes as $result) {
                if (!isset($result['success']) || !$result['success']) {
                    continue;
                }

                $imageData = $result['data'];
                $success |= (int)$this->processImage(
                    $this->paths->responsiveImagePath() . '/' . $breakpoint,
                    $imageData['url'],
                    $originalImage
                );
            }
        }

        if ($success > 0) {
            $this->messageEvent->send($this->startMessage($originalImage) . 'Responsive images generated');
        }
    }

    private function processImage(string $newBasePath, string $url, string $originalImage): bool
    {
        $fileName = $this->adminHelper->contractFileName($originalImage);
        $newPath = $newBasePath
            . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '.'
            . pathinfo($url, PATHINFO_EXTENSION);

        return $this->adminHelper->copyImage($url, $newPath);
    }


    protected function getBackupFilename(string $file): string
    {
        return $this->paths->backupImagesParentDir()
            . OptimizeImage::BACKUP_FOLDER_NAME
            . '/' . $this->adminHelper->contractFileName($file);
    }

    private function startMessage(string $originalImage): string
    {
        return $this->adminHelper->maskFileName($originalImage) . ': ';
    }
}
