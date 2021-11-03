<?php declare(strict_types=1);

namespace EasyAlipay\Tests\Crypto;

use const PHP_MAJOR_VERSION;

use function array_values;
use function file_get_contents;
use function is_string;
use function method_exists;
use function preg_match;
use function preg_replace;
use function random_bytes;
use function sprintf;
use function substr;

use EasyAlipay\Formatter;
use EasyAlipay\Crypto\Rsa;
use PHPUnit\Framework\TestCase;

class RsaTest extends TestCase
{
    private const BASE64_EXPRESSION = '#^[a-zA-Z0-9\+/]+={0,2}$#';

    private const FIXTURES = __DIR__ . '/../fixtures/mock.%s.%s';

    private const EVELOPE = '#-{5}BEGIN[^-----]+-{5}\r?\n(?<base64>[^-----]+)\r?\n-{5}END[^-----]+-{5}#';

    public function testClassConstants(): void
    {
        self::assertIsString(Rsa::ALGO_TYPE_RSA);
        self::assertIsString(Rsa::ALGO_TYPE_RSA2);
    }

    /**
     * @param string $type
     * @param string $suffix
     */
    private function getMockContents(string $type, string $suffix): string
    {
        $file = sprintf(self::FIXTURES, $type, $suffix);
        $pkey = file_get_contents($file);

        preg_match(self::EVELOPE, $pkey ?: '', $matches);

        return preg_replace('#\r|\n#', '', $matches['base64'] ?: '');
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
        } else {
            self::assertIsResource($pkey);
        }
    }

    public function testPkcs1ToSpki(): void
    {
        [, , [$spki], , , , , , , [$pkcs1]] = array_values($this->keyPhrasesDataProvider());

        self::assertStringStartsWith('public.spki://', $spki);
        self::assertStringStartsWith('public.pkcs1://', $pkcs1);
        self::assertEquals(substr($spki, 14), Rsa::pkcs1ToSpki(substr($pkcs1, 15)));
    }

    /**
     * @return array<string,array{string,boolean}>
     */
    public function pkcs1PhrasesDataProvider(): array
    {
        return [
            '`private.pkcs1://`' => [$this->getMockContents('pkcs1', 'key'), false],
            '`public.pkcs1://`'  => [$this->getMockContents('pkcs1', 'pem'), true],
        ];
    }

    /**
     * @dataProvider pkcs1PhrasesDataProvider
     *
     * @param string $thing
     */
    public function testFromPkcs1(string $thing, bool $isPublic = false): void
    {
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $thing);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $thing);
        }

        $pkey = Rsa::fromPkcs1($thing, $isPublic);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    public function testFromSpki(): void
    {
        $thing = $this->getMockContents('spki', 'pem');

        self::assertIsString($thing);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $thing);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $thing);
        }

        $pkey = Rsa::fromSpki($thing);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    /**
     * @return array<string,array{string,boolean|string}>
     */
    public function keyPhrasesDataProvider(): array
    {
        return [
            '`private.pkcs1://` string'               => ['private.pkcs1://' . $this->getMockContents('pkcs1', 'key'), Rsa::KEY_TYPE_PRIVATE],
            '`private.pkcs8://` string'               => ['private.pkcs8://' . $this->getMockContents('pkcs8', 'key'), Rsa::KEY_TYPE_PRIVATE],
            '`public.spki://` string'                 => ['public.spki://' . $this->getMockContents('spki', 'pem'),    Rsa::KEY_TYPE_PUBLIC],
            '`file://` PKCS#1 privateKey path string' => [$f = 'file://' . sprintf(self::FIXTURES, 'pkcs1', 'key'),  Rsa::KEY_TYPE_PRIVATE],
            'PKCS#1 privateKey contents'              => [(string)file_get_contents($f),                               Rsa::KEY_TYPE_PRIVATE],
            '`file://` PKCS#8 privateKey path string' => [$f = 'file://' . sprintf(self::FIXTURES, 'pkcs8', 'key'),  Rsa::KEY_TYPE_PRIVATE],
            'PKCS#8 privateKey contents'              => [(string)file_get_contents($f),                               Rsa::KEY_TYPE_PRIVATE],
            '`file://` SPKI publicKey path string'    => [$f = 'file://' . sprintf(self::FIXTURES, 'spki', 'pem'),   Rsa::KEY_TYPE_PUBLIC],
            'SKPI publicKey contents'                 => [(string)file_get_contents($f),                               Rsa::KEY_TYPE_PUBLIC],
            '`public.pkcs1://` string'                => ['public.pkcs1://' . $this->getMockContents('pkcs1', 'pem'),  Rsa::KEY_TYPE_PUBLIC],
        ];
    }

    /**
     * @dataProvider keyPhrasesDataProvider
     *
     * @param string $thing
     * @param boolean|string $type
     */
    public function testFrom(string $thing, $type): void
    {
        $pkey = Rsa::from($thing, $type);

        self::assertNotFalse($pkey);

        if (8 === PHP_MAJOR_VERSION) {
            self::assertIsObject($pkey);
        } else {
            self::assertIsResource($pkey);
        }
    }

    /**
     * @return array<string,array{string,\OpenSSLAsymmetricKey|resource|mixed,string,\OpenSSLAsymmetricKey|resource|mixed}>
     */
    public function keysProvider(): array
    {
        [[$pri1], [$pri2], [$pub1], [$pri3], [$pri4], [$pri5], [$pri6], [$pub2], [$pub3], [$pub4]] = array_values($this->keyPhrasesDataProvider());

        return [
            'plaintext, `private.pkcs1://`, `public.spki://`'               => [Formatter::nonce( 8), Rsa::fromPkcs1(substr($pri1, 16)), Rsa::ALGO_TYPE_RSA2, Rsa::fromSpki(substr($pub1, 14))],
            'plaintext, `private.pkcs8://`, `public.spki://`'               => [Formatter::nonce(16), Rsa::fromPkcs8(substr($pri2, 16)), Rsa::ALGO_TYPE_RSA2, Rsa::fromSpki(substr($pub1, 14))],
            'plaintext, `file://` PKCS#1 privateKey, `public.spki://`'      => [Formatter::nonce(24), Rsa::from($pri3), Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, PKCS#1 privateKey string, `public.spki://`'         => [Formatter::nonce(32), Rsa::from($pri4), Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `file://` PKCS#8 privatekey, `public.spki://`'      => [Formatter::nonce(40), Rsa::from($pri5), Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, PKCS#8 privateKey string, `public.spki://`'         => [Formatter::nonce(48), Rsa::from($pri6), Rsa::ALGO_TYPE_RSA2, $pub1],
            'plaintext, `private.pkcs1://`, `file://` SPKI pubKey'          => [Formatter::nonce(56), Rsa::from($pri1), Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `private.pkcs8://`, `file://` SPKI pubKey'          => [Formatter::nonce(64), Rsa::from($pri2), Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `file://` PKCS#1 privateKey, `file://` SPKI pubKey' => [Formatter::nonce(72), $pri3, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, PKCS#1 privateKey string, `file://` SPKI pubKey'    => [Formatter::nonce(80), $pri4, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `file://` PKCS#8 privatekey, `file://` SPKI pubKey' => [Formatter::nonce(88), $pri5, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, PKCS#8 privateKey string, `file://` SPKI pubKey'    => [Formatter::nonce(96), $pri6, Rsa::ALGO_TYPE_RSA2, $pub2],
            'plaintext, `private.pkcs1://`, SPKI publicKey string'          => [Formatter::nonce(104), $pri1, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `private.pkcs8://`, SPKI publicKey string'          => [Formatter::nonce(112), $pri2, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `file://` PKCS#1 privateKey, SPKI publicKey string' => [Formatter::nonce(120), $pri3, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, PKCS#1 privateKey string, SPKI publicKey string'    => [Formatter::nonce(128), $pri4, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `file://` PKCS#8 privatekey, SPKI publicKey string' => [Formatter::nonce(134), $pri5, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, PKCS#8 privateKey string, SPKI publicKey string'    => ['hello Alipay 你好 支付宝', $pri6, Rsa::ALGO_TYPE_RSA2, $pub3],
            'plaintext, `private.pkcs1://`, `public.pkcs1://`'              => [random_bytes(142), Rsa::fromPkcs1(substr($pri1, 16)), Rsa::ALGO_TYPE_RSA2, Rsa::fromPkcs1(substr($pub4, 15), true)],
            'plaintext, `private.pkcs8://`, `public.pkcs1://`'              => [random_bytes(150), Rsa::fromPkcs8(substr($pri2, 16)), Rsa::ALGO_TYPE_RSA2, Rsa::fromPkcs1(substr($pub4, 15), true)],
            'plaintext, `file://` PKCS#1 privateKey, `public.pkcs1://`'     => [random_bytes(158), Rsa::from($pri3), Rsa::ALGO_TYPE_RSA2, Rsa::fromPkcs1(substr($pub4, 15), true)],
            'plaintext, PKCS#1 privateKey string, `public.pkcs1://`'        => [random_bytes(166), Rsa::from($pri4), Rsa::ALGO_TYPE_RSA2, $pub4],
            'plaintext, `file://` PKCS#8 privatekey, `public.pkcs1://`'     => [random_bytes(174), Rsa::from($pri5), Rsa::ALGO_TYPE_RSA2, $pub4],
            'plaintext, PKCS#8 privateKey string, `public.pkcs1://`'        => [random_bytes(182), Rsa::from($pri6), Rsa::ALGO_TYPE_RSA2, $pub4],
        ];
    }

    /**
     * @dataProvider keysProvider
     * @param string $plaintext
     * @param string|\OpenSSLAsymmetricKey|resource|mixed $privateKey
     * @param string $type
     */
    public function testSign(string $plaintext, $privateKey, $type): void
    {
        $signature = Rsa::sign($plaintext, is_string($privateKey) ? Rsa::from($privateKey) : $privateKey, $type);

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
     * @param string|\OpenSSLAsymmetricKey|resource|mixed $privateKey
     * @param string $type
     * @param string|\OpenSSLAsymmetricKey|resource|mixed $publicKey
     */
    public function testVerify(string $plaintext, $privateKey, $type, $publicKey): void
    {
        $signature = Rsa::sign($plaintext, is_string($privateKey) ? Rsa::from($privateKey) : $privateKey, $type);

        self::assertIsString($signature);
        self::assertNotEquals($plaintext, $signature);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $signature);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $signature);
        }

        self::assertTrue(Rsa::verify($plaintext, $signature, is_string($publicKey) ? Rsa::from($publicKey, Rsa::KEY_TYPE_PUBLIC) : $publicKey, $type));
    }
}
