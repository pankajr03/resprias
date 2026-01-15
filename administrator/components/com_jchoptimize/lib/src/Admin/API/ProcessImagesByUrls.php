<?php

namespace JchOptimize\Core\Admin\API;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri as GuzzleUri;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\UriResolver;
use _JchOptimizeVendor\V91\Joomla\DI\Container;
use _JchOptimizeVendor\V91\Joomla\Input\Input;
use _JchOptimizeVendor\V91\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlUrl;
use _JchOptimizeVendor\V91\Spatie\Crawler\Exceptions\InvalidUrl;
use Exception;
use JchOptimize\Container\ContainerFactory;
use JchOptimize\Core\Admin\AdminHelper;
use JchOptimize\Core\Cdn\Cdn;
use JchOptimize\Core\Combiner;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Html\HtmlProcessor;
use JchOptimize\Core\Platform\PathsInterface;
use JchOptimize\Core\Registry;
use JchOptimize\Core\Spatie\Crawler;
use JchOptimize\Core\Spatie\Crawlers\HtmlCollector;
use JchOptimize\Core\Spatie\CrawlQueues\OptimizeImagesCrawlQueue;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Uri\Uri;
use JchOptimize\Core\Uri\UriConverter;
use JchOptimize\Core\Uri\UriNormalizer;
use JchOptimize\Core\Uri\Utils;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function file_exists;
use function filesize;
use function in_array;
use function preg_match;

class ProcessImagesByUrls extends AbstractProcessImages
{
    private array $processedImages = [];

    private FileImageQueue $imageQueue;

    private OptimizeImagesCrawlQueue $urlCrawlQueue;

    private bool $firstRun;

    private int $currentCrawlLimit = 1;

    private int $maxCrawlLimit;
    private bool $complete = false;

    public function __construct(Container $container, MessageEventInterface $messageEventObj)
    {
        parent::__construct($container, $messageEventObj);

        $input = $this->getContainer()->get(Input::class);
        $this->firstRun = $input->get('firstRun');

        $this->imageQueue = $this->getContainer()->get(FileImageQueue::class);
        $this->urlCrawlQueue = $this->getContainer()->get(OptimizeImagesCrawlQueue::class);

        $this->maxCrawlLimit = (int)$this->params->get('pro_api_crawl_limit', 15);
    }

    public function setCursor(?string $cursor): void
    {
        if ($cursor === null) {
            return;
        }

        $data = $this->decodeCursor($cursor);
        if ($data === null) {
            return;
        }

        $this->currentCrawlLimit = $data['currentCrawlLimit'];
        $this->prevFiles = $data['prevFiles'];
        $this->prevFileSize = $this->prevFiles['prevFileSize'];
    }

    public function getCursor(): ?string
    {
        $payload = [
            'v' => 'v1',
            'currentCrawlLimit' => $this->currentCrawlLimit,
            'prevFiles' => $this->prevFiles,
            'prevFileSize' => $this->prevFileSize
        ];

        return $this->encodeCursor($payload);
    }

    /**
     * @param float $startTime
     * @param float $maxExecutionTime
     * @return array{url?: string, images: array}
     */
    public function getFilePackages(float $startTime, float $maxExecutionTime): array
    {
        [$files, $totalFileSize] = $this->initializeFileArray();
        /** @var array{images: array, url?: string} $files */
        do {
            $imageWrapper = $this->getPendingImage();

            if ($imageWrapper === null) {
                break;
            }

            if (isset($files['url']) && $files['url'] !== (string)$imageWrapper->foundOnUrl) {
                return $files;
            }

            $files['url'] = (string)$imageWrapper->foundOnUrl;
            $image = (string)$imageWrapper->url;
            $fileSize = (int)filesize($image);

            if ($fileSize > $this->maxUploadFilesize) {
                $this->messageEventObj->send(
                    'Skipping ' . $this->adminHelper->maskFileName($image) . ': Too large!'
                );
                $this->markAsProcessed($imageWrapper);

                continue;
            }

            $totalFileSize += $fileSize;

            if ($totalFileSize > $this->maxUploadFilesize) {
                $this->prevFiles[] = $image;
                $this->prevFileSize = $fileSize;
                $this->markAsProcessed($imageWrapper);

                break;
            }

            $files['images'][] = $image;
            $this->markAsProcessed($imageWrapper);
        } while (
            count($files['images']) < $this->getMaxFileUploads()
            && microtime(true) - $startTime < $maxExecutionTime
        );

        if (!$this->hasPendingImages()) {
            $this->imageQueue->empty();
            $this->urlCrawlQueue->empty();
        }

        return $files;
    }

    private function markAsProcessed(CrawlUrl $imageWrapper): void
    {
        try {
            $this->imageQueue->markAsProcessed($imageWrapper);
        } catch (ExceptionInterface | InvalidUrl $e) {
        }
    }

    protected function getImagesFromPendingHtml(array $htmlCollection): void
    {
        $container = ContainerFactory::create();
        $this->setParamsForApiImages($container);

        foreach ($htmlCollection as $htmlArray) {
            $aHtmlImages = $this->getImagesInHtml($container, $htmlArray['html']);
            $aCssImages = $this->getImagesInCss($container);
            $images = array_merge($aHtmlImages, $aCssImages);
            $images = array_unique(array_filter($images));//Get the absolute file path of images on filesystem
            $uri = Utils::uriFor($htmlArray['url']);
            $images = array_map(function ($a) use ($uri, $container) {
                $uri = UriResolver::resolve($uri, UriNormalizer::normalize(Utils::uriFor($a)));
                $cdn = $container->get(Cdn::class);
                $pathsUtils = $container->get(PathsInterface::class);

                return UriConverter::uriToFilePath($uri, $pathsUtils, $cdn);
            }, $images);
            $images = array_filter($images, function ($a) {
                return preg_match('#' . AdminHelper::$optimizeImagesFileExtRegex . '#i', $a)
                    && !in_array($a, $this->processedImages)
                    && @file_exists($a);
            });//If option set, remove images already optimized
            if ($this->params->get('ignore_optimized', '1')) {
                $images = $this->adminHelper->filterOptimizedFiles($images);
            }
            $images = array_values(array_unique($images));

            foreach ($images as $image) {
                $imageWrapper = CrawlUrl::create(
                    Utils::uriFor($image),
                    Uri::withoutQueryValue($uri, 'jchnooptimize')
                );

                try {
                    if (!$this->imageQueue->hasAlreadyBeenProcessed($imageWrapper)) {
                        $this->imageQueue->add($imageWrapper);
                    }
                } catch (ExceptionInterface | InvalidUrl $e) {
                }
            }
        }
    }

    protected function getImagesInHtml(Container $container, string $html): array
    {
        $htmlProcessor = $container->getNewInstance(HtmlProcessor::class);
        $htmlProcessor->setHtml($html);

        return $htmlProcessor->processImagesForApi();
    }

    protected function getImagesInCss(Container $container): array
    {
        try {
            $htmlProcessor = $container->get(HtmlProcessor::class);
            $htmlProcessor->processCombineJsCss();
            $oFilesManager = $container->get(FilesManager::class);
            $aCssLinks = $oFilesManager->aCss;
            $oCombiner = $container->get(Combiner::class);
            $aResult = $oCombiner->combineFiles($aCssLinks[0]);
            $aCssImages = array_unique(array_filter($aResult->getImages(), function (mixed $a) {
                return $a instanceof UriInterface;
            }));
        } catch (Exception) {
            $aCssImages = [];
        }

        return $aCssImages;
    }

    protected function setParamsForApiImages(Container $container): void
    {
        $params = $container->get(Registry::class);
        $params->set('combine_files_enable', '1');
        $params->set('combine_files', '1');
        $params->set('javascript', '0');
        $params->set('css', '1');
        $params->set('css_minify', '0');
        $params->set('excludeCss', []);
        $params->set('excludeCssComponents', []);
        $params->set('replaceImports', '1');
        $params->set('phpAndExternal', '1');
        $params->set('inlineScripts', '1');
        $params->set('lazyload_enable', '0');
        $params->set('cookielessdomain_enable', '0');
        $params->set('optimizeCssDelivery_enable', '0');
        $params->set('csg_enable', '0');
    }

    public function hasPendingImages(): bool
    {
        if ($this->complete) {
            return false;
        }

        try {
            return $this->urlCrawlQueue->hasPendingUrls() || $this->imageQueue->hasPendingUrls() || $this->firstRun;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getPendingImage(): ?CrawlUrl
    {
        if ($this->complete) {
            return null;
        }

        try {
            if ($this->imageQueue->hasPendingUrls()) {
                return $this->imageQueue->getPendingUrl();
            }
            if ($this->currentCrawlLimit > $this->maxCrawlLimit) {
                $this->complete = true;

                return null;
            }
            if ($this->urlCrawlQueue->hasPendingUrls()) {
                $baseCrawlUrl = $this->urlCrawlQueue->getPendingUrl();

                if ($baseCrawlUrl) {
                    $this->crawlUrl($baseCrawlUrl->url);
                }

                return $this->getPendingImage();
            }
            if ($this->firstRun) {
                $paths = $this->getContainer()->get(PathsInterface::class);
                $baseUrl = (string)$this->params->get('pro_api_base_url', SystemUri::homePageAbsolute($paths));
                $this->crawlUrl(new GuzzleUri($baseUrl));
                $this->firstRun = false;

                return $this->getPendingImage();
            }
        } catch (ExceptionInterface | Exception $e) {
        }

        return null;
    }

    private function crawlUrl(UriInterface $baseUrl): void
    {
        $htmlCollector = new HtmlCollector();
        $htmlCollector->setEventLogging(true);
        $htmlCollector->setMessageEventObj($this->messageEventObj);
        $htmlCollector->setLogger($this->getContainer()->get(LoggerInterface::class));

        Crawler::create($baseUrl)
            ->setCrawlObserver($htmlCollector)
            ->setTotalCrawlLimit($this->currentCrawlLimit++)
            ->setCrawlQueue($this->urlCrawlQueue)
            ->startCrawling($baseUrl);
        $htmlArray = $htmlCollector->getHtmls();

        $this->getImagesFromPendingHtml($htmlArray);
    }
}
