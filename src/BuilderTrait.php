<?php declare(strict_types=1);

namespace EasyAlipay;

use function array_replace_recursive;
use function count;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Chainable points the client interface for sending HTTP requests.
 */
trait BuilderTrait
{
    abstract public function getDriver(): ClientDecoratorInterface;

    /**
     * Compose the remote `entryMethod`
     *
     * @param string $seperator - The `entryMethod` seperator, default is dot(`.`) character
     */
    abstract protected function entryMethod(string $seperator = '.'): string;

    /**
     * Prepare the Request options to apply.
     *
     * @param string $verb - The Request HTTP verb, one of `GET` or `POST`
     * @param array<string,mixed> $things - Request options to apply.
     *
     * @return array<string,mixed>
     */
    protected function prepare(string $verb, array ...$things): array
    {
        $method = ['method' => $this->entryMethod()];
        switch(count($things)) {
            case 0:
                return ['query' => $method];
            case 1:
                [$options] = $things;
                return array_replace_recursive($options, ['query' => $method]);
            case 2:
                [$thing, $options] = $things;
                return array_replace_recursive($options, $verb === 'GET' ? ['query' => $thing] : ['content' => $thing], ['query' => $method]);
            default:
                [$content, $query, $options] = $things;
                return array_replace_recursive($options, ['query' => $query, 'content' => $content], ['query' => $method]);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(array ...$options): ResponseInterface
    {
        return $this->getDriver()->request($verb = 'GET', '', $this->prepare($verb, ...$options));
    }

    /**
     * @inheritDoc
     */
    public function post(array ...$options): ResponseInterface
    {
        return $this->getDriver()->request($verb = 'POST', '', $this->prepare($verb, ...$options));
    }

    /**
     * @inheritDoc
     */
    public function getAsync(array ...$options): PromiseInterface
    {
        return $this->getDriver()->requestAsync($verb = 'GET', '', $this->prepare($verb, ...$options));
    }

    /**
     * @inheritDoc
     */
    public function postAsync(array ...$options): PromiseInterface
    {
        return $this->getDriver()->requestAsync($verb = 'POST', '', $this->prepare($verb, ...$options));
    }
}
