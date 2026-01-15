<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler\CrawlObservers;

use ArrayAccess;
use _JchOptimizeVendor\V91\GuzzleHttp\Exception\RequestException;
use Iterator;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\V91\Spatie\Crawler\CrawlUrl;

class CrawlObserverCollection implements ArrayAccess, Iterator
{
    protected int $position;
    public function __construct(protected array $observers = [])
    {
        $this->position = 0;
    }
    public function addObserver(CrawlObserver $observer): void
    {
        $this->observers[] = $observer;
    }
    public function crawled(CrawlUrl $crawlUrl, ResponseInterface $response): void
    {
        foreach ($this->observers as $crawlObserver) {
            $crawlObserver->crawled($crawlUrl->url, $response, $crawlUrl->foundOnUrl);
        }
    }
    public function crawlFailed(CrawlUrl $crawlUrl, RequestException $exception): void
    {
        foreach ($this->observers as $crawlObserver) {
            $crawlObserver->crawlFailed($crawlUrl->url, $exception, $crawlUrl->foundOnUrl);
        }
    }
    #[\ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->observers[$this->position];
    }
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->observers[$offset] ?? null;
    }
    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->observers[] = $value;
        } else {
            $this->observers[$offset] = $value;
        }
    }
    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->observers[$offset]);
    }
    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->observers[$offset]);
    }
    #[\ReturnTypeWillChange]
    public function next(): void
    {
        $this->position++;
    }
    #[\ReturnTypeWillChange]
    public function key(): mixed
    {
        return $this->position;
    }
    #[\ReturnTypeWillChange]
    public function valid(): bool
    {
        return isset($this->observers[$this->position]);
    }
    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->position = 0;
    }
}
