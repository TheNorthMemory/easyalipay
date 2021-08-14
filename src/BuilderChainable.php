<?php declare(strict_types=1);

namespace EasyAlipay;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Signature of the Chainable `GuzzleHttp\Client` interface
 */
interface BuilderChainable
{
    /**
     * `$driver` getter
     */
    public function getDriver(): ClientDecoratorInterface;

    /**
     * Chainable the given `$method` with the `ClientDecoratorInterface` instance
     *
     * @param string $method - The sgement or `URI`
     */
    public function chain(string $method): BuilderChainable;

    /**
     * Create and send an HTTP GET request.
     *
     * @param array<string,mixed>[] $options - Request options to apply.
     */
    public function get(array ...$options): ResponseInterface;

    /**
     * Create and send an HTTP POST request.
     *
     * @param array<string,mixed>[] $options - Request options to apply.
     */
    public function post(array ...$options): ResponseInterface;

    /**
     * Create and send an asynchronous HTTP GET request.
     *
     * @param array<string,mixed>[] $options - Request options to apply.
     */
    public function getAsync(array ...$options): PromiseInterface;

    /**
     * Create and send an asynchronous HTTP POST request.
     *
     * @param array<string,mixed>[] $options - Request options to apply.
     */
    public function postAsync(array ...$options): PromiseInterface;
}
