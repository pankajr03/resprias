<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\CrawlQueues;

use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlUrl;

interface CrawlQueue
{
    public function add(CrawlUrl $url): self;
    public function has(CrawlUrl|UriInterface $crawlUrl): bool;
    public function hasPendingUrls(): bool;
    public function getUrlById($id): CrawlUrl;
    public function getPendingUrl(): ?CrawlUrl;
    public function hasAlreadyBeenProcessed(CrawlUrl $url): bool;
    public function markAsProcessed(CrawlUrl $crawlUrl): void;
    public function getProcessedUrlCount(): int;
}
