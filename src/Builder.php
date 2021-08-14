<?php declare(strict_types=1);

namespace EasyAlipay;

use function array_filter;
use function array_push;
use function implode;
use function preg_replace_callback_array;
use function strtolower;

use ArrayIterator;

/**
 * Chainable the client for sending HTTP requests.
 */
final class Builder
{
    /**
     * Building & decorate the chainable `\GuzzleHttp\Client`
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
     * ```php
     * // usage samples
     * $instance = Builder::factory([]);
     * $instance->chain('alipay.offline.market.shop.category.query')->get(['debug' => true]);
     * $instance->Alipay->Offline->Market->Shop->Category->Query->getAsync(['debug' => true])->wait();
     * ```
     * @param array<string,mixed> $config - configuration .
     */
    public static function factory(array $config = []): BuilderChainable
    {
        return new class([], new ClientDecorator($config)) extends ArrayIterator implements BuilderChainable
        {
            use BuilderTrait;

            /**
             * Compose the chainable `ClientDecoratorInterface` instance, most starter with the tree root point
             * @param string[] $input
             * @param ClientDecoratorInterface $instance
             */
            public function __construct(array $input, ClientDecoratorInterface $instance) {
                parent::__construct($input, self::STD_PROP_LIST | self::ARRAY_AS_PROPS);

                $this->driver = &$instance;
            }

            /**
             * @var ClientDecoratorInterface $driver
             */
            protected $driver;

            /**
             * @inheritDoc
             */
            public function getDriver(): ClientDecoratorInterface
            {
                return $this->driver;
            }

            /**
             * Normalize the `$thing` by the rules: `PascalCase` -> `camelCase` & `dotNotation` -> `dot.notation`
             *
             * @param string $thing - The string waiting for normalization
             */
            protected function normalize(string $thing = ''): string
            {
                return preg_replace_callback_array([
                    '#^[A-Z]#' => static function(array $v): string { return strtolower($v[0]); },
                    '#[A-Z]#'  => static function(array $v): string { return strtolower('.' . $v[0]); },
                ], $thing) ?? $thing;
            }

            /**
             * Compose the remote OpenAPI `method`
             *
             * @param string $seperator - The OpenAPI `method` seperator, default is dot(`.`) character
             */
            protected function entryMethod(string $seperator = '.'): string
            {
                return implode($seperator, $this->simplized());
            }

            /**
             * Only retrieve a copy array of the `method` segments
             *
             * @return string[] - The `method` segments array
             */
            protected function simplized(): array
            {
                return array_filter($this->getArrayCopy(), static function($v) { return !($v instanceof BuilderChainable); });
            }

            /**
             * @inheritDoc
             */
            public function offsetGet($key): BuilderChainable
            {
                if (false === $this->offsetExists($key)) {
                  $index = $this->simplized();
                  array_push($index, $this->normalize($key));
                  $this->offsetSet($key, new self($index, $this->getDriver()));
                }

                return parent::offsetGet($key);
            }

            /**
             * @inheritDoc
             */
            public function chain(string $method): BuilderChainable
            {
                return $this->offsetGet($method);
            }
        };
    }

    private function __construct()
    {
        // cannot be instantiated
    }
}
