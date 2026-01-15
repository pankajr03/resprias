<?php

namespace _JchOptimizeVendor\V91\Spatie\Crawler;

use _JchOptimizeVendor\V91\GuzzleHttp\Psr7\Response;
use _JchOptimizeVendor\V91\Psr\Http\Message\ResponseInterface;

class ResponseWithCachedBody extends Response
{
    protected ?string $cachedBody = null;
    public static function fromGuzzlePsr7Response(ResponseInterface $response): static
    {
        return new static($response->getStatusCode(), $response->getHeaders(), $response->getBody(), $response->getProtocolVersion(), $response->getReasonPhrase());
    }
    public function setCachedBody(?string $body = null): void
    {
        $this->cachedBody = $body;
    }
    public function getCachedBody(): ?string
    {
        return $this->cachedBody;
    }
}
