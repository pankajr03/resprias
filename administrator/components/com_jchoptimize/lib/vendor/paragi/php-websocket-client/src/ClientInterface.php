<?php

/*
 * Websocket client - https://github.com/paragi/PHP-websocket-client
 */
namespace _JchOptimizeVendor\V91\Paragi\PhpWebsocket;

/**
 * Contract for a websocket client
 */
interface ClientInterface
{
    public function read(&$error_string = null);
    public function write($data, bool $final = \true, bool $binary = \true);
}
