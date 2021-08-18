<?php declare(strict_types=1);

namespace EasyAlipay\Tests\Crypto;

use function md5;
use function implode;
use function strlen;

use EasyAlipay\Formatter;
use EasyAlipay\Helpers;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/mock.%s.%s';

    private const PEM_CERT_EVELOPE = '#-{5}BEGIN CERTIFICATE-{5}\r?\n(?<base64>[^-----]+)\r?\n-{5}END CERTIFICATE-{5}#';

    public function testClassConstants(): void
    {
        self::assertIsString(Helpers::CERT_PEM);
        self::assertIsString(Helpers::CERT_ATTR);
    }

    /**
     * @return array<string,array{string[],string,string,int}>
     */
    public function md5DataProvider(): array
    {
        return [
            'only one argument in' => [
                $txt = [Formatter::nonce(30)],
                md5(implode('', $txt)),
                'assertEquals',
                32,
            ],
            'more than one arguments in' => [
                $txt = [Formatter::nonce(30), Formatter::nonce(30)],
                md5(implode('', $txt)),
                'assertEquals',
                32,
            ],
        ];
    }

    /**
     * @dataProvider md5DataProvider
     * @param string[] $things
     * @param string $digest
     * @param string $action
     * @param int $length
     */
    public function testMd5(array $things, string $digest, string $action, int $length): void
    {
        $excepted = Helpers::md5(...$things);
        self::assertIsString($digest);
        self::assertNotEmpty($digest);
        self::assertEquals(strlen($digest), $length);
        self::{$action}($excepted, $digest);
    }

    /**
     * @return array<string,array{string}>
     */
    public function loadDataProvider(): array
    {
        return [
            'normal local file'             => [$file = sprintf(self::FIXTURES, 'sha256', 'crt')],
            'normal file:// protocol'       => ['file://' . $file],
            'RFC2397 data:// protocol'      => ['data://text/plain;base64,' . base64_encode($c = (string)file_get_contents($file))],
            'data:// with two certificates' => ['data://text/plain;base64,' . base64_encode(PHP_EOL . $c . PHP_EOL . PHP_EOL . $c . PHP_EOL)],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     * @param string $file
     */
    public function testLoad(string $file): void
    {
        $things = Helpers::load($file);
        self::assertIsArray($things);
        self::assertArrayHasKey(0, $things);

        /** @var array<string,mixed> $thing */
        [$thing] = $things;
        self::assertIsArray($thing);
        self::assertArrayHasKey(Helpers::CERT_PEM, $thing);
        self::assertArrayHasKey(Helpers::CERT_ATTR, $thing);

        [Helpers::CERT_PEM => $pem, Helpers::CERT_ATTR => $attr] = $thing;

        self::assertIsString($pem);
        self::assertNotEmpty($pem);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::PEM_CERT_EVELOPE, $pem);
        } else {
            self::assertRegExp(self::PEM_CERT_EVELOPE, $pem);
        }

        /** @var array<string,mixed> $attr */
        self::assertIsArray($attr);
        self::assertArrayHasKey('issuer', $attr);
        self::assertArrayHasKey('subject', $attr);
        self::assertArrayHasKey('serialNumber', $attr);
        self::assertArrayHasKey('serialNumberHex', $attr);
        self::assertArrayHasKey('signatureTypeLN', $attr);
    }

    /**
     * @dataProvider loadDataProvider
     * @param string $file
     */
    public function testExtract(string $file): void
    {
        $thing = Helpers::extract($file);
        self::assertIsString($thing);
        self::assertNotEmpty($thing);
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::PEM_CERT_EVELOPE, $thing);
        } else {
            self::assertRegExp(self::PEM_CERT_EVELOPE, $thing);
        }
    }

    /**
     * @dataProvider loadDataProvider
     * @param string $file
     */
    public function testSn(string $file): void
    {
        $thing = Helpers::sn($file);
        self::assertIsString($thing);
        self::assertNotEmpty($thing);
        if (strpos($thing, '_') > 0) {
            $tmp = explode('_', $thing);
            array_walk($tmp, static function (string $piece): void {
                static::assertTrue(strlen($piece) === 32);
            });
        } else {
            self::assertTrue(strlen($thing) === 32);
        }
    }

    /**
     * @return array<string,array{array<string,string>,string}>
     */
    public function foldDataProvider(): array
    {
        return [
            'nest array{a:b,c:d}, colleped as `c=d,a=b`' => [
                ['a' => 'b', 'c' => 'd'], 'c=d,a=b',
            ],
            'nest array{C,O,OU,CN}, colleped as `CN,OU,O,C`' => [
                ['C' => 'CN', 'O' => 'EACommunity', 'OU' => 'EACommunity Authority', 'CN' => 'EACommunity CA R0'],
                'CN=EACommunity CA R0,OU=EACommunity Authority,O=EACommunity,C=CN',
            ],
        ];
    }

    /**
     * @dataProvider foldDataProvider
     * @param array<string,string> $things
     * @param string $excepted
     */
    public function testFold(array $things, string $excepted): void
    {
        $thing = Helpers::fold($things);
        self::assertIsString($thing);
        self::assertNotEmpty($thing);
        self::assertEquals($thing, $excepted);
    }
}
