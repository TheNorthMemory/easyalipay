<?php declare(strict_types=1);

namespace EasyAlipay;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_OS;
use const PHP_QUERY_RFC1738;
use const PHP_VERSION;

use function array_push;
use function array_replace_recursive;
use function extension_loaded;
use function function_exists;
use function implode;
use function is_callable;
use function is_string;
use function php_uname;
use function sprintf;
use function str_replace;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Decorate the `\GuzzleHttp\Client` instance
 */
final class ClientDecorator implements ClientDecoratorInterface
{
    /** @var Client $client */
    protected $client;

    /** @var HandlerStack $stack */
    protected $stack;

    /**
     * @var array<string,string|array<string,string>> - The defaults configuration whose pased in `GuzzleHttp\Client`.
     */
    protected static $defaults = [
        'base_uri' => 'https://openapi.alipay.com/gateway.do',
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ],
        'params' => [
            'charset'   => 'UTF-8',
            'format'    => 'JSON',
            'sign_type' => 'RSA2',
            'version'   => '1.0',
        ],
    ];

    /**
     * Deep merge the input with the defaults
     *
     * @param array<string,mixed> $config - The configuration.
     *
     * @return array<string,mixed> - With the built-in configuration.
     */
    protected static function withDefaults(array $config = []): array
    {
        return array_replace_recursive(static::$defaults, ['headers' => static::userAgent()], $config);
    }

    /**
     * Prepare the `User-Agent` key/value pair
     *
     * @return array<string,string>
     */
    protected static function userAgent(): array
    {
        $value = [
            sprintf('EasyAlipay/%d.%d', static::MAJOR_VERSION, static::MINOR_VERSION),
            sprintf('GuzzleHttp/%d', Client::MAJOR_VERSION),
        ];

        extension_loaded('curl') && function_exists('curl_version') && array_push($value, 'curl/' . ((array)curl_version())['version']);

        array_push($value, sprintf('(%s/%s) PHP/%s', PHP_OS, php_uname('r'), PHP_VERSION));

        return ['User-Agent' => implode(' ', $value)];
    }

    /**
     * Taken body string
     *
     * @param MessageInterface $message
     */
    protected static function body(MessageInterface $message): string
    {
        $stream = $message->getBody();
        $content = (string)$stream;

        $stream->tell() && $stream->rewind();

        return $content;
    }

    /**
     * Builtin pager local service
     *
     * @param RequestInterface $request
     * @param array<string,mixed> $query - The http query
     * @param array<string,mixed> $data - The additional data
     */
    protected static function pager(RequestInterface $request, array $query = [], array $data = []): PromiseInterface
    {
        return Create::promiseFor(new Response(200, [],
            Formatter::page(
                (string)$request->getUri()->withQuery(''),
                $request->getMethod(), $query, $data
            )
        ));
    }

    /**
     * @param \OpenSSLAsymmetricKey|resource|string|mixed $privateKey - The merchant privateKey.
     *
     * @return callable(callable(RequestInterface,array<string,mixed>))
     */
    public static function signer($privateKey): callable
    {
        return static function(callable $handler) use ($privateKey): callable {
            return static function(RequestInterface $request, array $options) use ($handler, $privateKey): PromiseInterface {
                $data = ['biz_content' => json_encode($options['content'] ?? (object)[], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];
                ['params' => $params] = $options;
                $params['timestamp'] = $params['timestamp'] ?? Formatter::localeDateTime();

                $query = Query::parse($request->getUri()->getQuery()) + $params;
                $signature = Crypto\Rsa::sign(Formatter::queryStringLike(Formatter::ksort($data + $query)), $privateKey);
                $data += ['sign' => $signature];

                if (isset($options['pager']) && ($pager = $options['pager'])) {
                    return is_callable($pager) ? $pager($request, $query, $data) : static::pager($request, $query, $data);
                }

                $modify = [];
                if ('GET' === $request->getMethod() || $request->getBody() instanceof MultipartStream) {
                    $query += $data;
                } else {
                    $modify += ['body' => Query::build($data, PHP_QUERY_RFC1738)];
                }
                $modify += ['query' => Query::build($query, PHP_QUERY_RFC1738)];

                unset($options['query'], $options['params'], $options['content']);

                return $handler(Utils::modifyRequest($request, $modify), $options);
            };
        };
    }

    /**
     * @param \OpenSSLAsymmetricKey|\OpenSSLCertificate|object|resource|string $publicKey The platform publicKey
     *
     * @return callable(callable(RequestInterface,array<string,mixed>))
     */
    public static function verifier($publicKey): callable
    {
        return static function(callable $handler) use ($publicKey): callable {
            return static function(RequestInterface $request, array $options) use ($publicKey, $handler): PromiseInterface {
                return $handler($request, $options)->then(static function(ResponseInterface $response) use ($publicKey): ResponseInterface {
                    /**
                     * @var ?string $payload
                     * @var ?string $sign
                     * @var ?string $ident
                     */
                    ['payload' => $payload, 'sign' => $sign, 'ident' => $ident] = Formatter::fromJsonLike(static::body($response));
                    $verified = is_string($payload) && is_string($sign) && Crypto\Rsa::verify($payload, $sign, $publicKey);
                    return $response
                        ->withAddedHeader('X-Alipay-Signature', $sign ?? '')
                        ->withAddedHeader('X-Alipay-Responder', str_replace('_', '.', $ident ?? ''))
                        ->withAddedHeader('X-Alipay-Verified', $verified ? 'ok' : '')
                        ->withBody(Utils::streamFor($payload ?? $response->getBody()));
                });
            };
        };
    }

    /**
     * Decorate the `\GuzzleHttp\Client` factory
     *
     * Acceptable \$config parameters stucture
     *   - privateKey: \OpenSSLAsymmetricKey|object|resource|string - The merchant private key.
     *   - publicKey: \OpenSSLAsymmetricKey|object|resource|string - The platform public key.
     *   - params?: array{app_id?:string, app_auth_token?:string, app_cert_sn?:string, alipay_root_cert_sn?:string}
     *   - params<?app_id, string> - The app_id string. (optional)
     *   - params<?app_auth_token, string> - The ISV auth token string. (optional)
     *   - params<?app_cert_sn, string> - The MD5 string of the merchant's X509 certificate issuer&serial attributes. (optional)
     *   - params<?alipay_root_cert_sn, string> - The MD5 string of the platform's X509 certificate(s) issuer&serial attributes. (optional)
     *
     * @param array<string,mixed> $config
     */
    public function __construct(array $config = [])
    {
        /** @var HandlerStack $stack */
        $stack = isset($config['handler']) && ($config['handler'] instanceof HandlerStack) ? $config['handler'] : HandlerStack::create();
        $stack->before('prepare_body', static::signer($config['privateKey'] ?? ''), 'signer');
        $stack->before('http_errors', static::verifier($config['publicKey'] ?? ''), 'verifier');
        $this->stack = $config['handler'] = $stack;

        $this->client = new Client(static::withDefaults($config));
    }

    /**
     * @inheritDoc
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->stack;
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function requestAsync(string $method, string $uri = '', array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($method, $uri, $options);
    }
}
