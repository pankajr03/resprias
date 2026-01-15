<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\Handlers;

use Exception;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\ConnectException;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\Spatie\Crawler\Crawler;

class CrawlRequestFailed
{
    public function __construct(protected Crawler $crawler)
    {
        //
    }
    public function __invoke(Exception $exception, $index)
    {
        if ($exception instanceof ConnectException) {
            $exception = new RequestException($exception->getMessage(), $exception->getRequest());
        }
        if ($exception instanceof RequestException) {
            $crawlUrl = $this->crawler->getCrawlQueue()->getUrlById($index);
            $this->crawler->getCrawlObservers()->crawlFailed($crawlUrl, $exception);
        }
        usleep($this->crawler->getDelayBetweenRequests());
    }
}
