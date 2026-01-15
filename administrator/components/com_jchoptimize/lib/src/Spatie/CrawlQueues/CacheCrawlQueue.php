<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Spatie\CrawlQueues;

use _JchOptimizeVendor\V91\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\ClearByNamespaceInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\IterableInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\OptimizableInterface;
use _JchOptimizeVendor\V91\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlQueues\CrawlQueue;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlUrl;
use _JchOptimizeVendor\V91\Spatie\Crawler\Exceptions\InvalidUrl;
use _JchOptimizeVendor\V91\Spatie\Crawler\Exceptions\UrlNotFoundByIndex;
use Exception;

use function bin2hex;
use function iterator_count;
use function md5;
use function random_bytes;

class CacheCrawlQueue implements CrawlQueue
{
    /** @var string */
    protected const URLS_NAMESPACE = 'jchoptimize_urls';

    /** @var string */
    protected const PENDING_URLS_NAMESPACE = 'jchoptimize_pending_urls';

    private string $runId;

    public function __construct(
        /**
         * @var StorageInterface&ClearByNamespaceInterface&IterableInterface
         */
        protected $storage,
        /**
         * @var StorageInterface&ClearByNamespaceInterface&IterableInterface
         */
        protected $pendingStorage
    ) {
        $this->setStorageNamespace();
    }

    protected function setStorageNamespace(): void
    {
        try {
            $this->runId = bin2hex(random_bytes(8));
        } catch (Exception $ignored) {
            $this->runId = '';
        }


        $this->storage->getOptions()->setNamespace(self::URLS_NAMESPACE . ':' . $this->runId);
        $this->pendingStorage->getOptions()->setNamespace(self::PENDING_URLS_NAMESPACE . ':' . $this->runId);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function add(CrawlUrl $url): CrawlQueue
    {
        $urlId = $this->getUrlId($url);
        $url->setId($urlId);

        $this->storage->addItem($urlId, $url);
        $this->pendingStorage->addItem($urlId, $url);

        return $this;
    }

    /**
     * @throws InvalidUrl
     * @throws ExceptionInterface
     */
    public function has($crawlUrl): bool
    {
        if ($crawlUrl instanceof CrawlUrl || $crawlUrl instanceof UriInterface) {
            return $this->storage->hasItem($this->getUrlId($crawlUrl));
        }

        throw InvalidUrl::unexpectedType($crawlUrl);
    }

    /**
     * @throws Exception
     */
    public function hasPendingUrls(): bool
    {
        return (bool)iterator_count($this->pendingStorage);
    }

    /**
     * @param string $id
     * @return CrawlUrl
     * @throws ExceptionInterface
     */
    public function getUrlById($id): CrawlUrl
    {
        $result = $this->storage->getItem($id);

        if (!$result instanceof CrawlUrl) {
            throw new UrlNotFoundByIndex("Crawl url with id {$id} not found in cache");
        }

        return $result;
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function getPendingUrl(): ?CrawlUrl
    {
        foreach ($this->pendingStorage as $item) {
            return $this->pendingStorage->getItem($item);
        }

        return null;
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function hasAlreadyBeenProcessed(CrawlUrl $url): bool
    {
        $id = $this->getUrlId($url);

        if ($this->pendingStorage->hasItem($id)) {
            return false;
        }

        if ($this->storage->hasItem($id)) {
            return true;
        }

        return false;
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function markAsProcessed(CrawlUrl $crawlUrl): void
    {
        $id = $this->getUrlId($crawlUrl);

        $this->pendingStorage->removeItem($id);
    }

    /**
     * @throws Exception
     */
    public function getProcessedUrlCount(): int
    {
        return iterator_count($this->storage) - iterator_count($this->pendingStorage);
    }

    /**
     * @throws InvalidUrl
     */
    protected function getUrlId($crawlUrl): string
    {
        if ($crawlUrl instanceof CrawlUrl) {
            return md5((string)$crawlUrl->url);
        }

        if ($crawlUrl instanceof UriInterface) {
            return md5((string)$crawlUrl);
        }

        throw InvalidUrl::unexpectedType($crawlUrl);
    }

    public function __destruct()
    {
        $this->emptyStorage($this->storage, self::URLS_NAMESPACE . ':' . $this->runId);
        $this->emptyStorage($this->pendingStorage, self::PENDING_URLS_NAMESPACE . ':' . $this->runId);
    }



    protected function emptyStorage(StorageInterface $storage, $nameSpace): void
    {
        if ($storage instanceof ClearByNamespaceInterface) {
            $storage->clearByNameSpace($nameSpace);
        }
        if ($storage instanceof OptimizableInterface) {
            $storage->optimize();
        }
    }
}
