<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\CrawlProfiles;

use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;

abstract class CrawlProfile
{
    abstract public function shouldCrawl(UriInterface $url): bool;
}
