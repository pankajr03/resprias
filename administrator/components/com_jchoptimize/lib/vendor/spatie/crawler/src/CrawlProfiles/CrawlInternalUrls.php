<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\CrawlProfiles;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;

class CrawlInternalUrls extends CrawlProfile
{
    protected mixed $baseUrl;
    public function __construct($baseUrl)
    {
        if (!$baseUrl instanceof UriInterface) {
            $baseUrl = new Uri($baseUrl);
        }
        $this->baseUrl = $baseUrl;
    }
    public function shouldCrawl(UriInterface $url): bool
    {
        return $this->baseUrl->getHost() === $url->getHost();
    }
}
