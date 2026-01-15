<?php

namespace JchOptimize\Core\Spatie;

use _JchOptimizeVendor\V91\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\Crawler as SpatieCrawler;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

use const JCH_VERSION;

class Crawler
{
    public static function create(UriInterface|string $baseUrl): SpatieCrawler
    {
        return SpatieCrawler::create(self::crawlClientOptions())
            ->setParseableMimeTypes(['text/html'])
            ->ignoreRobots()
            ->setCrawlProfile(new CrawlInternalUrls($baseUrl));
    }

    public static function crawlClientOptions(): array
    {
        return[
            RequestOptions::COOKIES => false,
            RequestOptions::CONNECT_TIMEOUT => 100,
            RequestOptions::TIMEOUT => 100,
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HEADERS => [
                'User-Agent' => 'JchOptimizeCrawler/' . JCH_VERSION
            ]
        ];
    }
}
