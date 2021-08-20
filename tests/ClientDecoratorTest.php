<?php declare(strict_types=1);

namespace EasyAlipay\Tests;

use function array_map;
use function json_decode;
use function json_encode;
use function openssl_pkey_get_private;
use function openssl_pkey_get_public;
use function sprintf;
use function strlen;
use function strval;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

use ReflectionClass;
use ReflectionMethod;

use EasyAlipay\Formatter;
use EasyAlipay\Crypto\Rsa;
use EasyAlipay\ClientDecorator;
use EasyAlipay\ClientDecoratorInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\TestCase;

class ClientDecoratorTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/mock.%s.%s';

    private const CONTENT_LENGTH   = 'Content-Length';
    private const CONTENT_TYPE     = 'Content-Type';
    private const CONTENT_LANGUAGE = 'Content-Language';

    private const X_ALIPAY_SIGNATURE = 'X-Alipay-Signature';
    private const X_ALIPAY_RESPONDER = 'X-Alipay-Responder';
    private const X_ALIPAY_VERIFIED  = 'X-Alipay-Verified';

    public function testClassConstants(): void
    {
        self::assertIsInt(ClientDecorator::MAJOR_VERSION);
        self::assertIsInt(ClientDecorator::MINOR_VERSION);
    }

    public function testByReflectionClass(): void
    {
        $ref = new ReflectionClass(ClientDecorator::class);
        self::assertInstanceOf(ReflectionClass::class, $ref);

        $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
        self::assertIsArray($methods);
        self::assertNotEmpty($methods);

        self::assertTrue($ref->isFinal());
        self::assertTrue($ref->hasMethod('signer'));
        self::assertTrue($ref->hasMethod('verifier'));
        self::assertTrue($ref->hasMethod('getClient'));
        self::assertTrue($ref->hasMethod('getHandlerStack'));
        self::assertTrue($ref->hasMethod('request'));
        self::assertTrue($ref->hasMethod('requestAsync'));
    }

    /** @var MockHandler $mock */
    private $mock;

    private function guzzleMockStack(): HandlerStack
    {
        $this->mock = new MockHandler();

        return HandlerStack::create($this->mock);
    }

    /**
     * @return array<string,array{array<string,mixed>}>
     */
    public function constructorSuccessProvider(): array
    {
        return [
            'default' => [
                [
                    'privateKey' => '------BEGIN PRIVATE KEY------',
                    'publicKey'  => '-----BEGIN PUBLIC KEY-----',
                ],
            ],
            'with base_uri' => [
                [
                    'privateKey' => '------BEGIN PRIVATE KEY------',
                    'publicKey'  => '-----BEGIN PUBLIC KEY-----',
                    'base_uri'   => 'https://openapi.alipay.com/gateway.do',
                ],
            ],
            'with base_uri and handler' => [
                [
                    'privateKey' => '------BEGIN PRIVATE KEY------',
                    'publicKey'  => '-----BEGIN PUBLIC KEY-----',
                    'base_uri'   => 'https://openapi.test.alipay.com/gateway.do',
                    'handler'    => $this->guzzleMockStack(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider constructorSuccessProvider
     *
     * @param array<string,mixed> $config
     */
    public function testGetClient(array $config): void
    {
        $instance = new ClientDecorator($config);

        self::assertInstanceOf(ClientDecoratorInterface::class, $instance);

        $client = $instance->getClient();
        self::assertInstanceOf(\GuzzleHttp\Client::class, $client);
    }

    /**
     * @dataProvider constructorSuccessProvider
     *
     * @param array<string,mixed> $config
     */
    public function testGetHandlerStack(array $config): void
    {
        $instance = new ClientDecorator($config);

        self::assertInstanceOf(ClientDecoratorInterface::class, $instance);

        $stack = $instance->getHandlerStack();
        self::assertInstanceOf(HandlerStack::class, $stack);

        $stackDebugInfo = strval($stack);
        self::assertStringContainsString('verifier', $stackDebugInfo);
        self::assertStringContainsString('signer', $stackDebugInfo);
    }

    /**
     * @return array{\OpenSSLAsymmetricKey|resource|string|mixed,\OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|string|mixed}
     */
    private function mockConfiguration(): array
    {
        $privateKey = openssl_pkey_get_private('file://' . sprintf(static::FIXTURES, 'pkcs8', 'key'));
        $publicKey  = openssl_pkey_get_public('file://' . sprintf(static::FIXTURES, 'spki', 'pem'));

        if (false === $privateKey || false === $publicKey) {
            throw new \Exception('Loading the pkey failed.');
        }

        return [$privateKey, $publicKey];
    }

    /**
     * @param int $status
     * @param array<string,string[]|string> $headers
     * @param string $body
     */
    private static function pickResponse(int $status, array $headers, string $body = ''): ResponseInterface
    {
        return new Response($status, $headers, $body);
    }

    /**
     * @return array<string,array{mixed,mixed,string,string,ResponseInterface,string}>
     */
    public function withMockHandlerProvider(): array
    {
        [$privateKey, $publicKey] = $this->mockConfiguration();
        /** @var array<string,string> $available */
        $available = [
            static::CONTENT_LANGUAGE => 'zh-CN',
            static::CONTENT_LENGTH   => '150',
            static::CONTENT_TYPE     => 'text/html;charset=GBK',
        ];
        /** @var array<string,string> $unavailable */
        $unavailable = [static::CONTENT_LENGTH => '0'];

        return [
            'HTTP 200, `GET` with `UTF-8` GOT `GBK` content in incompleted format' => [
                $privateKey, $publicKey, 'GET', '',
                static::pickResponse(200, $available,
                    $body = '<!DOCTYPE html><html>head></head><style></style><div id="Header"><script></script></div><div id="Info"></div><div id="Foot"></div><!--footer ending-->'),
                $length = '150',
            ],
            'HTTP 200, `POST` with `UTF-8` GOT `GBK` content in incompleted format' => [
                $privateKey, $publicKey, 'POST', '',
                static::pickResponse(200, $available, $body), $length,
            ],
            'HTTP 200 `PATCH` with `UTF-8` GOT nothing' => [
                $privateKey, $publicKey, 'PATCH', '',
                static::pickResponse(200, $unavailable), $length = '0',
            ],
            'HTTP 200 `PUT` with `UTF-8` GOT nothing' => [
                $privateKey, $publicKey, 'PUT', '',
                static::pickResponse(200, $unavailable), $length,
            ],
            'HTTP 200 `DELETE` with `UTF-8` GOT nothing' => [
                $privateKey, $publicKey, 'DELETE', '',
                static::pickResponse(200, $unavailable), $length,
            ],
        ];
    }

    /**
     * @dataProvider withMockHandlerProvider
     *
     * @param mixed $privateKey
     * @param mixed $publicKey
     * @param string $method
     * @param string $uri
     * @param ResponseInterface $response
     * @param string $contentLength
     */
    public function testRequestsWithMockHandler($privateKey, $publicKey, string $method, string $uri, ResponseInterface $response, string $contentLength): void
    {
        $instance = new ClientDecorator([
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
            'handler'    => $this->guzzleMockStack(),
        ]);

        $this->mock->reset();
        $this->mock->append($response);

        $res = $instance->request($method, $uri);
        self::assertTrue($res->hasHeader(static::CONTENT_LENGTH));
        /** @var string $length */
        [$length] = $res->getHeader(static::CONTENT_LENGTH);
        self::assertIsString($length);
        self::assertEquals($length, $contentLength);
        if ($length !== '0') {
            self::assertTrue($res->hasHeader(static::CONTENT_TYPE));
            self::assertTrue($res->hasHeader(static::CONTENT_LANGUAGE));
        }
    }

    /**
     * @dataProvider withMockHandlerProvider
     *
     * @param mixed $privateKey
     * @param mixed $publicKey
     * @param string $method
     * @param string $uri
     * @param ResponseInterface $response
     * @param string $contentLength
     */
    public function testAsyncRequestsWithMockHandler($privateKey, $publicKey, string $method, string $uri, ResponseInterface $response, string $contentLength): void
    {
        $instance = new ClientDecorator([
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
            'handler'    => $this->guzzleMockStack(),
        ]);

        $mock = $this->mock;
        $mock->reset();
        $mock->append($response);

        $instance->requestAsync($method, $uri)->then(static function($res) use ($contentLength, $mock, $method, $uri) {
            /** @var \GuzzleHttp\Psr7\Request $req */
            $req = $mock->getLastRequest();
            static::assertInstanceOf(\GuzzleHttp\Psr7\Request::class, $req);
            static::assertEquals($method, $req->getMethod());
            static::assertNotEquals($uri, $req->getRequestTarget());
            static::assertTrue($res->hasHeader(static::CONTENT_LENGTH));
            /** @var string $length */
            [$length] = $res->getHeader(static::CONTENT_LENGTH);
            static::assertIsString($length);
            static::assertEquals($length, $contentLength);
            if ($length !== '0') {
                static::assertTrue($res->hasHeader(static::CONTENT_TYPE));
                static::assertTrue($res->hasHeader(static::CONTENT_LANGUAGE));
            }
        })->wait();
    }

    /**
     * @param RequestInterface $request
     * @param string $appId
     * @param string $entryMethod
     * @param array<string,mixed> $bizContent
     * @param \OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|string|mixed $publicKey
     */
    private static function verification(RequestInterface $request, string $appId, string $entryMethod, array $bizContent, $publicKey): void
    {
        $query = $request->getUri()->getQuery();
        static::assertNotEmpty($query);
        static::assertStringContainsString('app_id=' . $appId, $query);
        static::assertStringContainsString('method=' . $entryMethod, $query);

        $params = Query::parse($query);
        array_map(static function($key) use($params): void {
            static::assertArrayHasKey($key, $params);
        }, ['app_id', 'method', 'timestamp', 'format', 'charset', 'sign_type', 'version']);

        $body = (string)$request->getBody();
        $data = Query::parse($body);
        static::assertNotEmpty($data);
        array_map(static function($key) use($data): void {
            static::assertArrayHasKey($key, $data);
        }, ['biz_content', 'sign']);

        static::assertEquals($bizContent, (array)json_decode($data['biz_content'], true));
        static::assertTrue(Rsa::verify(Formatter::queryStringLike(Formatter::ksort($data + $params)), $data['sign'], $publicKey));
    }

    /**
     * @return array<string,array{mixed,mixed,string,string,string,string,array<string,mixed>,callable<ResponseInterface>}>
     */
    public function normalRequestsDataProvider(): array
    {
        [$privateKey, $publicKey] = $this->mockConfiguration();

        return [
            'easy.alipay.ping' => [
                $privateKey, $publicKey, $appId = '2014072300007148', 'POST', '', $entryMethod = 'easy.alipay.ping',
                $content = ['app_id' => $appId, 'user_id' => 'abcd1234'],
                static function(RequestInterface $request) use ($privateKey, $publicKey, $appId, $entryMethod, $content): ResponseInterface {
                    static::verification($request, $appId, $entryMethod, $content, $publicKey);

                    $body = sprintf(
                        '{"%s%s":%s,"sign":"%s"}',
                        str_replace('.', '_', $entryMethod), '_response',
                        $payload = (string)json_encode($content, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
                        Rsa::sign($payload, $privateKey)
                    );
                    /** @var array<string,mixed> $headers */
                    $headers = [static::CONTENT_LENGTH => strlen($body), static::CONTENT_TYPE => 'text/html;charset=utf-8'];

                    return static::pickResponse(200, $headers, $body);
                },
            ],
        ];
    }

    /**
     * @dataProvider normalRequestsDataProvider
     *
     * @param mixed $privateKey
     * @param mixed $publicKey
     * @param string $appId
     * @param string $method
     * @param string $uri
     * @param string $entryMethod
     * @param array<string,mixed> $content
     * @param callable $respondor
     */
    public function testRequest($privateKey, $publicKey, string $appId, string $method, string $uri,
        string $entryMethod, array $content, callable $respondor): void
    {
        $instance = new ClientDecorator([
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
            'params'     => ['app_id' => $appId],
            'handler'    => $this->guzzleMockStack(),
        ]);

        $this->mock->reset();
        $this->mock->append($respondor);
        $res = $instance->request($method, $uri, ['query' => ['method' => $entryMethod], 'content' => $content]);

        array_map(static function($key) use($res): void {
            static::assertTrue($res->hasHeader($key));
        }, [static::X_ALIPAY_RESPONDER, static::X_ALIPAY_VERIFIED, static::X_ALIPAY_SIGNATURE]);

        self::assertEquals('ok', $res->getHeaderLine(static::X_ALIPAY_VERIFIED));
        self::assertEquals($content, json_decode((string)$res->getBody(), true));
    }

    /**
     * @dataProvider normalRequestsDataProvider
     *
     * @param mixed $privateKey
     * @param mixed $publicKey
     * @param string $appId
     * @param string $method
     * @param string $uri
     * @param string $entryMethod
     * @param array<string,mixed> $content
     * @param callable $respondor
     */
    public function testRequestAsync($privateKey, $publicKey, string $appId, string $method, string $uri,
        string $entryMethod, array $content, callable $respondor): void
    {
        $instance = new ClientDecorator([
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
            'params'     => ['app_id' => $appId],
            'handler'    => $this->guzzleMockStack(),
        ]);

        $this->mock->reset();
        $this->mock->append($respondor);

        $instance->requestAsync($method, $uri, ['query' => ['method' => $entryMethod], 'content' => $content])
        ->then(static function(ResponseInterface $res) use($content) {
            array_map(static function($key) use($res): void {
                static::assertTrue($res->hasHeader($key));
            }, [static::X_ALIPAY_RESPONDER, static::X_ALIPAY_VERIFIED, static::X_ALIPAY_SIGNATURE]);
            static::assertEquals('ok', $res->getHeaderLine(static::X_ALIPAY_VERIFIED));
            static::assertEquals($content, json_decode((string)$res->getBody(), true));
        })->wait();
    }
}
