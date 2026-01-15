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

namespace JchOptimize\Core\Spatie\Crawlers;

use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareInterface;
use _JchOptimizeVendor\V91\Psr\Log\LoggerAwareTrait;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlObservers\CrawlObserver;
use JchOptimize\Core\Admin\API\MessageEventInterface;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Uri\Uri;

class HtmlCollector extends CrawlObserver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var list<array{url:string, html:string}>
     */
    private array $htmls = [];

    private int $numUrls = 0;
    private bool $eventLogging = false;

    private ?MessageEventInterface $messageEventObj = null;

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null): void
    {
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();

        if (Helper::validateHtml($html)) {
            $this->htmls[] = [
                'url' => (string)$url,
                'html' => $html
            ];
        }

        if ($this->eventLogging) {
            $originalUrl = Uri::withoutQueryValue($url, 'jchnooptimize');
            $message = 'Crawled URL: ' . $originalUrl;
            $this->logger->info($message);
            $this->messageEventObj?->send($message);
            $this->numUrls++;
        }
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ): void {
        if ($this->eventLogging) {
            $message = 'Failed crawling url: ' . Uri::withoutQueryValue(
                $url,
                'jchnooptimize'
            ) . ' with message ' . $requestException->getMessage();

            $this->logger->error($message);

            $this->messageEventObj?->send($message);
        }
    }

    /**
     * @return void
     */
    public function finishedCrawling(): void
    {
       /* if ($this->eventLogging) {
           $this->messageEventObj?->send('Finished crawling ' . $this->numUrls . ' URLs');
        } */
    }

    /**
     * @return list<array{url:string, html:string}>
     */
    public function getHtmls(): array
    {
        return $this->htmls;
    }


    public function setEventLogging(bool $eventLogging): void
    {
        $this->eventLogging = $eventLogging;
    }

    public function setMessageEventObj(?MessageEventInterface $messageEventObj): void
    {
        $this->messageEventObj = $messageEventObj;
    }
}
