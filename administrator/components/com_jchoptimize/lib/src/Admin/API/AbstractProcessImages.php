<?php

namespace JchOptimize\Core\Admin\API;

use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\V91\Joomla\DI\ContainerAwareTrait;
use JchOptimize\Core\Admin\AdminHelper as AdminHelper;
use JchOptimize\Core\Registry;

use function base64_decode;
use function base64_encode;
use function connection_aborted;
use function ini_get;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_UNESCAPED_SLASHES;

abstract class AbstractProcessImages implements ProcessImagesQueueInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected float $maxUploadFilesize;

    protected float $maxFileUploads;

    protected Registry $params;

    protected array $prevFiles = [];

    protected float $prevFileSize = 0;

    protected AdminHelper $adminHelper;

    public function __construct(Container $container, protected MessageEventInterface $messageEventObj)
    {
        $this->setContainer($container);
        $this->params = $container->get(Registry::class);
        $this->adminHelper = $container->get(AdminHelper::class);

        $maxFileSize = $this->params->get('pro_api_max_size', '2M') ?: ini_get('upload_max_filesize');
        $this->maxUploadFilesize = 0.8 * $this->adminHelper->stringToBytes($maxFileSize);
        $this->maxFileUploads = 0.8 * (int)ini_get('max_file_uploads');
    }

    /**
     * @return array{0: array{images: array}, 1: float}
     */
    protected function initializeFileArray(): array
    {
        $files = [
            'images' => $this->prevFiles
        ];
        $totalFileSize = $this->prevFileSize;
        $this->prevFiles = [];
        $this->prevFileSize = 0;

        return [$files, $totalFileSize];
    }

    protected function getMaxFileUploads(): float
    {
        if (connection_aborted()) {
            exit();
        }

        $numFiles = (float)$this->params->get('pro_api_num_files');

        if ($numFiles) {
            return min($numFiles, $this->maxFileUploads);
        }

        return $this->maxFileUploads;
    }

    protected function decodeCursor(string $cursor): ?array
    {
        $data = json_decode(base64_decode($cursor), true);
        if (!is_array($data) || ($data['v'] ?? null) !== 'v1') {
            return null;
        }

        return $data;
    }

    protected function encodeCursor(array $payload): string
    {
        return base64_encode((string)json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
