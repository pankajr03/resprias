<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\Ajax;

use _JchOptimizeVendor\V91\GuzzleHttp\Client;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\ConnectException;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\GuzzleHttp\Pool;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\MultipartStream;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Request;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use _JchOptimizeVendor\V91\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\V91\Psr\Http\Client\ClientInterface;
use _JchOptimizeVendor\V91\Psr\Log\NullLogger;
use Exception;
use Generator;
use JchOptimize\Core\Admin\API\FulfillImageOptimization;
use JchOptimize\Core\Admin\API\MessageEventFactory;
use JchOptimize\Core\Admin\API\MessageEventInterface;
use JchOptimize\Core\Admin\API\ProcessImagesByFolders;
use JchOptimize\Core\Admin\API\ProcessImagesByUrls;
use JchOptimize\Core\Admin\API\ProcessImagesQueueInterface;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Registry;
use Throwable;

use function array_map;
use function array_merge;
use function class_exists;
use function count;
use function defined;
use function file_exists;
use function microtime;
use function set_time_limit;
use function sleep;
use function sprintf;
use function ucfirst;

defined('_JCH_EXEC') or die('Restricted access');

class OptimizeImage extends Ajax
{
    public const BACKUP_FOLDER_NAME = 'jch_optimize_backup_images';

    private MessageEventInterface $messageEventObj;

    private FulfillImageOptimization $fulfillImageOptimization;

    protected function __construct()
    {
        parent::__construct();

        $this->messageEventObj = MessageEventFactory::create(
            $this->input->getString('evtMsg'),
            $this->input->get('browserId')
        );

        $this->fulfillImageOptimization = new FulfillImageOptimization(
            $this->messageEventObj,
            $this->logger ?? new NullLogger(),
            $this->adminHelper,
            $this->paths
        );

        set_time_limit(0);
    }

    public function run(): void
    {
        try {
            $this->messageEventObj->initialize();
            $startTime = microtime(true);

            while (true) {
                try {
                    $message = $this->messageEventObj->receive($this->input);
                    if (is_object($message)) {
                        if ($message->type == 'optimize') {
                            $this->optimize($message->data);
                        } elseif ($message->type == 'disconnected') {
                            $this->messageEventObj->disconnect();
                        }
                        break;
                    }
                    if (microtime(true) - $startTime > 30) {
                        break;
                    }
                    sleep(1);
                } catch (Exception $e) {
                    $this->messageEventObj->send("PHP error: {$e->getMessage()}", 'apiError');
                    break;
                }
            }
        } catch (Throwable $e) {
            echo "Error: {$e->getMessage()}\n";
        }
    }

    public function optimize(object $data): void
    {
        if (isset($data->subdirs)) {
            $subDirs = array_map([$this, 'resolveDirectories'], $data->subdirs);
        }

        if (!empty($subDirs)) {
            $this->input->set('subdirs', $subDirs);
        }

        $options = new Registry($data->params, '/');

        if (isset($data->filepack)) {
            $files = [];
            foreach ($data->filepack as $file) {
                $files[] = $file->path;
                if (isset($file->path) && (isset($file->width) || isset($file->height))) {
                    $shortFileName = $this->adminHelper->createClientFileName($file->path);
                    $options->set("resize/$shortFileName/width", (int)$file->width ?: 0);
                    $options->set("resize/$shortFileName/height", (int)$file->height ?: 0);
                }
            }
            $this->input->set('files', $files);
        }
        $this->input->set('firstRun', $data->firstRun);

        $cursor = isset($data->cursor) ? (string)$data->cursor : null;

        /** @var Client&ClientInterface $client */
        $client = $this->getContainer()->get(ClientInterface::class);

        $requests = $this->getFilePackageRequestsBatch($options, $cursor);
        $pool = new Pool($client, $requests, [
            'concurrency' => (int)$this->getContainer()->get(Registry::class)->get('pro_api_concurrency', 5),
            'options' => [
                RequestOptions::SYNCHRONOUS => false,
                RequestOptions::TIMEOUT => (int)$this->params->get('api_connection_timeout', 30),
            ],
            'fulfilled' => [$this->fulfillImageOptimization, 'handle'],
            'rejected' => [$this, 'rejectedRequests']
        ]);

        $pool->promise()->wait();

        $batchMeta = $requests->getReturn();
        $this->messageEventObj->send((string)json_encode($batchMeta), 'batchComplete');
        $this->messageEventObj->disconnect();
    }

    private function resolveDirectories(string $directory): string
    {
        return $this->adminHelper->normalizePath(
            Helper::appendTrailingSlash($this->paths->rootPath()) . Helper::removeLeadingSlash($directory)
        );
    }

    public function rejectedRequests(RequestException|ConnectException $exception, $index): void
    {
        foreach ($index as $file) {
            $fileName = $this->adminHelper->maskFileName($file);
            $message = $fileName . ': Request failed with message: ' . $exception->getMessage();
            $this->messageEventObj->send($message, 'requestRejected');
        }
    }

    private function getFilePackageRequestsBatch(Registry $options, ?string $cursor): Generator
    {
        $mode = $this->input->get('mode');
        /** @see ProcessImagesByUrls */
        /** @see ProcessImagesByFolders */
        $imageProcessorClass = '\JchOptimize\Core\Admin\API\ProcessImages' . ucfirst($mode);
        $processedThisBatch = 0;
        $totalFilesFoundThisBatch = 0;
        $startTime = microtime(true);
        $maxExecutionTime = 10;
        $reqCount = 0;
        $maxRequests = 25;

        if (!class_exists($imageProcessorClass)) {
            $msg = sprintf('Image processor class %s not found', $imageProcessorClass);
            $this->logger?->error($msg);
            $this->messageEventObj->send($msg, 'apiError');

            return ['finished' => true, 'cursor' => null];
        }

        $imageProcessor = new $imageProcessorClass($this->getContainer(), $this->messageEventObj);

        if (!$imageProcessor instanceof ProcessImagesQueueInterface) {
            $msg = sprintf('Class %s not instance of %s', $imageProcessorClass, ProcessImagesQueueInterface::class);
            $this->logger?->error($msg);
            $this->messageEventObj->send($msg, 'apiError');

            return ['finished' => true, 'cursor' => null];
        }

        $imageProcessor->setCursor($cursor);

        while ($imageProcessor->hasPendingImages()) {
            $files = $imageProcessor->getFilePackages($startTime, $maxExecutionTime);

            if (empty($files['images'])) {
                continue;
            }

            $this->packageCroppedImages($options, $files['images']);
            $noImagesInPackage = count($files['images']);
            $totalFilesFoundThisBatch += $noImagesInPackage;
            $this->messageEventObj->send((string)$noImagesInPackage, 'addFileCount');

            $uploadFiles = [];
            foreach ($files['images'] as $i => $file) {
                try {
                    $contents = GuzzlePsr7Utils::tryFopen($file, 'r');
                } catch (Exception) {
                    $contents = '';
                }

                if (file_exists($file)) {
                    $uploadFiles[] = [
                        'name' => 'files[' . $i . ']',
                        'contents' => $contents,
                        'filename' => $this->adminHelper->createClientFileName($file)
                    ];
                }
            }

            if (isset($files['url'])) {
                $options->set('url', $files['url']);
            }

            $data = ['name' => 'data', 'contents' => $options->toString()];
            $body = array_merge($uploadFiles, [$data]);

            yield $files['images'] => new Request(
                'POST',
                'https://api3.jch-optimize.net/api/optimize-images',
                [],
                new MultipartStream($body)
            );

            $processedThisBatch += $noImagesInPackage;

            // Stop once we exhaust the execution time
            if (++$reqCount >= $maxRequests || microtime(true) - $startTime >= $maxExecutionTime) {
                break;
            }
        }

        $nextCursor = $imageProcessor->getCursor();

        if ($totalFilesFoundThisBatch === 0) {
            $this->messageEventObj->send('0', 'addFileCount');
        }

        return [
            'cursor' => $nextCursor,
            'finished' => !$imageProcessor->hasPendingImages(),
            'finalLogDir' => $this->paths->getLogsPath(),
            'batch' => [
                'processed' => $processedThisBatch,
                'found' => $totalFilesFoundThisBatch
            ]
        ];
    }

    private function packageCroppedImages(Registry $options, array $images): void
    {
        $croppedImages = $options->get('cropgravity', []);

        foreach ($images as $image) {
            foreach ($croppedImages as $croppedImage) {
                if (
                    !empty($croppedImage->url)
                    && Helper::findExcludes([$croppedImage->url], $image)
                    && isset($croppedImage->gravity)
                    && isset($croppedImage->cropwidth)
                ) {
                    $shortFileName = $this->adminHelper->createClientFileName($image);
                    $options->set("resize/$shortFileName/crop", true);
                    $options->set("resize/$shortFileName/gravity", $croppedImage->gravity);
                    $options->set("resize/$shortFileName/width", $croppedImage->cropwidth);
                }
            }
        }

        $options->remove('cropgravity');
    }
}
