<?php declare(strict_types=1);

namespace EasyAlipay\Tests\Crypto;

use const PHP_MAJOR_VERSION;

use function array_values;
use function file_get_contents;
use function method_exists;
use function preg_match;
use function preg_replace;
use function sprintf;
use function strncasecmp;

use OpenSSLAsymmetricKey;

use EasyAlipay\Crypto\Rsa;
use PHPUnit\Framework\TestCase;

class RsaTest extends TestCase
{
    private const BASE64_EXPRESSION = '#^[a-zA-Z0-9\+/]+={0,2}$#';

    private const FIXTURES = __DIR__ . '/../fixtures/mock.%s.%s';

    private const EVELOPE = '#-{5}BEGIN[^-----]+-{5}\r?\n(?<base64>[^-----]+)\r?\n-{5}END[^-----]+-{5}#';

    /**
     * @param string $type
     * @param string $suffix
     */
    private function getMockContents(string $type, string $suffix): string
    {
        $file = sprintf(static::FIXTURES, $type, $suffix);
        $pkey = file_get_contents($file);

        preg_match(static::EVELOPE, $pkey ?: '', $matches);

        return preg_replace('#\r?\n#', '', $matches['base64'] ?: '');
    }

    public function testFromPkcs8(): void
    {
        $thing = $this->getMockContents('pkcs8', 'key');

        self::assertIsString($thing);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $thing);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $thing);
        }

        $pkey = Rsa::fromPkcs8($thing);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
            self::assertInstanceOf(OpenSSLAsymmetricKey::class, $pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    public function testFromPkcs1(): void
    {
        $thing = $this->getMockContents('pkcs8', 'key');

        self::assertIsString($thing);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $thing);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $thing);
        }

        $pkey = Rsa::fromPkcs1($thing);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
            self::assertInstanceOf(OpenSSLAsymmetricKey::class, $pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    public function testFromSpki(): void
    {
        $thing = $this->getMockContents('spki', 'pub');

        self::assertIsString($thing);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $thing);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $thing);
        }

        $pkey = Rsa::fromSpki($thing);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
            self::assertInstanceOf(OpenSSLAsymmetricKey::class, $pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    /**
     * @return array<string,array{string}>
     */
    public function keyPhrasesDataProvider(): array
    {
        return [
            '`private.pkcs1://` string'               => ['private.pkcs1://' . $this->getMockContents('pkcs1', 'key')],
            '`private.pkcs8://` string'               => ['private.pkcs8://' . $this->getMockContents('pkcs8', 'key')],
            '`public.spki://` string'                 => ['public.spki://' . $this->getMockContents('spki', 'pub')],
            '`file://` PKCS#1 privateKey path string' => [$f = 'file://' . sprintf(static::FIXTURES, 'pkcs1', 'key')],
            'PKCS#1 privateKey contents'              => [(string)file_get_contents($f)],
            '`file://` PKCS#8 privateKey path string' => [$f = 'file://' . sprintf(static::FIXTURES, 'pkcs8', 'key')],
            'PKCS#8 privateKey contents'              => [(string)file_get_contents($f)],
            '`file://` SPKI publicKey path string'    => [$f = 'file://' . sprintf(static::FIXTURES, 'spki', 'pub')],
            'SKPI publicKey contents'                 => [(string)file_get_contents($f)],
        ];
    }

    /**
     * @dataProvider keyPhrasesDataProvider
     *
     * @param string $thing
     */
    public function testFrom(string $thing): void
    {
        $pkey = Rsa::from($thing);

        self::assertIsString($pkey);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            if (0 !== strncasecmp('file://', $pkey, 7)) {
                $this->assertMatchesRegularExpression(self::EVELOPE, $pkey);
            }
        } else {
            if (0 !== strncasecmp('file://', $pkey, 7)) {
                self::assertRegExp(self::EVELOPE, $pkey);
            }
        }
    }

    /**
     * @return array<string,array{string,string,string,string}>
     */
    public function keysProvider(): array
    {
        [[$pri1], [$pri2], [$pub1], [$pri3], [$pri4], [$pri5], [$pri6], [$pub2], [$pub3]] = array_values($this->keyPhrasesDataProvider());

        return [
            'plaintext, `private.pkcs1://`, `public.spki://`'               => ['hello Alipay 你好 支付宝', $pri1, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `private.pkcs8://`, `public.spki://`'               => ['hello Alipay 你好 支付宝', $pri2, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `file://` PKCS#1 privateKey, `public.spki://`'      => ['hello Alipay 你好 支付宝', $pri3, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, PKCS#1 privateKey string, `public.spki://`'         => ['hello Alipay 你好 支付宝', $pri4, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `file://` PKCS#8 privatekey, `public.spki://`'      => ['hello Alipay 你好 支付宝', $pri5, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, PKCS#8 privateKey string, `public.spki://`'         => ['hello Alipay 你好 支付宝', $pri6, Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `private.pkcs1://`, `file://` SPKI pubKey'          => ['hello Alipay 你好 支付宝', $pri1, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `private.pkcs8://`, `file://` SPKI pubKey'          => ['hello Alipay 你好 支付宝', $pri2, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `file://` PKCS#1 privateKey, `file://` SPKI pubKey' => ['hello Alipay 你好 支付宝', $pri3, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, PKCS#1 privateKey string, `file://` SPKI pubKey'    => ['hello Alipay 你好 支付宝', $pri4, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `file://` PKCS#8 privatekey, `file://` SPKI pubKey' => ['hello Alipay 你好 支付宝', $pri5, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, PKCS#8 privateKey string, `file://` SPKI pubKey'    => ['hello Alipay 你好 支付宝', $pri6, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `private.pkcs1://`, SPKI publicKey string'          => ['hello Alipay 你好 支付宝', $pri1, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `private.pkcs8://`, SPKI publicKey string'          => ['hello Alipay 你好 支付宝', $pri2, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `file://` PKCS#1 privateKey, SPKI publicKey string' => ['hello Alipay 你好 支付宝', $pri3, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, PKCS#1 privateKey string, SPKI publicKey string'    => ['hello Alipay 你好 支付宝', $pri4, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `file://` PKCS#8 privatekey, SPKI publicKey string' => ['hello Alipay 你好 支付宝', $pri5, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, PKCS#8 privateKey string, SPKI publicKey string'    => ['hello Alipay 你好 支付宝', $pri6, Rsa::ALGO_TYPE_RSA2, $pub3],
        ];
    }

    /**
     * @dataProvider keysProvider
     * @param string $plaintext
     * @param string $privateKey
     * @param string $type
     */
    public function testSign(string $plaintext, $privateKey, $type): void
    {
        $signature = Rsa::sign($plaintext, Rsa::from($privateKey), $type);

        self::assertIsString($signature);
        self::assertNotEquals($plaintext, $signature);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $signature);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $signature);
        }
    }

    /**
     * @dataProvider keysProvider
     * @param string $plaintext
     * @param string $privateKey
     * @param string $type
     * @param string $publicKey
     */
    public function testVerify(string $plaintext, $privateKey, $type, $publicKey): void
    {
        $signature = Rsa::sign($plaintext, Rsa::from($privateKey), $type);

        self::assertIsString($signature);
        self::assertNotEquals($plaintext, $signature);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $signature);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $signature);
        }

        self::assertTrue(Rsa::verify($plaintext, $signature, Rsa::from($publicKey), $type));
    }
}
