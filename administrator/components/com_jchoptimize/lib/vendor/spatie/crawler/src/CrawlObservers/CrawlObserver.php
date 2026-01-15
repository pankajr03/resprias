<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\CrawlObservers;

use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;

abstract class CrawlObserver
{
    /**
     * Called when the crawler will crawl the url.
     *
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url): void
    {
    }
    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\UriInterface $url
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface $response
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    abstract public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null): void;
    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\UriInterface $url
     * @param \_JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException $requestException
     * @param \_JchOptimizeVendor\V91\Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    abstract public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null): void;
    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
    }
}
