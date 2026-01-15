<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\Handlers;

use Exception;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Utils;
use _JchOptimizeVendor\V91\GuzzleHttp\RedirectMiddleware;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\StreamInterface;
use _JchOptimizeVendor\V91\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\Crawler;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlerRobots;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlProfiles\CrawlSubdomains;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlUrl;
use _JchOptimizeVendor\V91\Spatie\Crawler\LinkAdder;
use _JchOptimizeVendor\V91\Spatie\Crawler\ResponseWithCachedBody;

class CrawlRequestFulfilled
{
    protected LinkAdder $linkAdder;
    public function __construct(protected Crawler $crawler)
    {
        $this->linkAdder = new LinkAdder($this->crawler);
    }
    public function __invoke(ResponseInterface $response, $index)
    {
        $body = $this->getBody($response);
        $robots = new CrawlerRobots($response->getHeaders(), $body, $this->crawler->mustRespectRobots());
        $crawlUrl = $this->crawler->getCrawlQueue()->getUrlById($index);
        if ($this->crawler->mayExecuteJavaScript()) {
            $body = $this->getBodyAfterExecutingJavaScript($crawlUrl->url);
            $response = $response->withBody(Utils::streamFor($body));
        }
        $responseWithCachedBody = ResponseWithCachedBody::fromGuzzlePsr7Response($response);
        $responseWithCachedBody->setCachedBody($body);
        if ($robots->mayIndex()) {
            $this->handleCrawled($responseWithCachedBody, $crawlUrl);
        }
        if (!$this->crawler->getCrawlProfile() instanceof CrawlSubdomains) {
            if ($crawlUrl->url->getHost() !== $this->crawler->getBaseUrl()->getHost()) {
                return;
            }
        }
        if (!$robots->mayFollow()) {
            return;
        }
        $baseUrl = $this->getBaseUrl($response, $crawlUrl);
        $this->linkAdder->addFromHtml($body, $baseUrl);
        usleep($this->crawler->getDelayBetweenRequests());
    }
    protected function getBaseUrl(ResponseInterface $response, CrawlUrl $crawlUrl): Uri
    {
        $redirectHistory = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
        if (empty($redirectHistory)) {
            return $crawlUrl->url;
        }
        return new Uri(end($redirectHistory));
    }
    protected function handleCrawled(ResponseInterface $response, CrawlUrl $crawlUrl): void
    {
        $this->crawler->getCrawlObservers()->crawled($crawlUrl, $response);
    }
    protected function getBody(ResponseInterface $response): string
    {
        $contentType = $response->getHeaderLine('Content-Type');
        if (!$this->isMimetypeAllowedToParse($contentType)) {
            return '';
        }
        return $this->convertBodyToString($response->getBody(), $this->crawler->getMaximumResponseSize());
    }
    protected function convertBodyToString(StreamInterface $bodyStream, $readMaximumBytes = 1024 * 1024 * 2): string
    {
        if ($bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }
        $body = '';
        $chunksToRead = $readMaximumBytes < 512 ? $readMaximumBytes : 512;
        for ($bytesRead = 0; $bytesRead < $readMaximumBytes; $bytesRead += $chunksToRead) {
            try {
                $newDataRead = $bodyStream->read($chunksToRead);
            } catch (Exception $exception) {
                $newDataRead = null;
            }
            if (!$newDataRead) {
                break;
            }
            $body .= $newDataRead;
        }
        return $body;
    }
    protected function getBodyAfterExecutingJavaScript(UriInterface $url): string
    {
        $browsershot = $this->crawler->getBrowsershot();
        $html = $browsershot->setUrl((string) $url)->bodyHtml();
        return html_entity_decode($html);
    }
    protected function isMimetypeAllowedToParse($contentType): bool
    {
        if (empty($contentType)) {
            return \true;
        }
        if (!count($this->crawler->getParseableMimeTypes())) {
            return \true;
        }
        foreach ($this->crawler->getParseableMimeTypes() as $allowedType) {
            if (stristr($contentType, $allowedType)) {
                return \true;
            }
        }
        return \false;
    }
}
