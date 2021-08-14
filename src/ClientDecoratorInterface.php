<?php declare(strict_types=1);

namespace EasyAlipay;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Decorate the `\GuzzleHttp\Client` interface
 */
interface ClientDecoratorInterface
{
    public const MAJOR_VERSION = 0;

    public const MINOR_VERSION = 1;

    /**
     * Retrieve the `\GuzzleHttp\HandlerStack` instance.
     */
    public function getHandlerStack(): HandlerStack;

    /**
     * Create and send an HTTP request.
     *
     * @param string $uri - The uri string.
     * @param string $method - The method string.
     * @param array<string,mixed> $options - Request options to apply. See \GuzzleHttp\RequestOptions.
     */
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface;

    /**
     * Create and send an asynchronous HTTP request.
     *
     * @param string $uri - The uri string.
     * @param string $method - The method string.
     * @param array<string,mixed> $options - Request options to apply. See \GuzzleHttp\RequestOptions.
     */
    public function requestAsync(string $method, string $uri = '', array $options = []): PromiseInterface;
}
