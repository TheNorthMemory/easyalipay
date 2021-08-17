<?php declare(strict_types=1);

namespace EasyAlipay\Tests\Crypto;

use function base64_encode;
use function random_bytes;
use function is_array;
use function is_null;

use EasyAlipay\Formatter;
use EasyAlipay\Crypto\AesCbc;
use PHPUnit\Framework\TestCase;

class AesCbcTest extends TestCase
{
    private const BASE64_EXPRESSION = '#^[a-zA-Z0-9\+/]+={0,2}$#';

    public function testClassConstants(): void
    {
        self::assertIsInt(AesCbc::BLOCK_SIZE);
    }

    /**
     * @return array<string,array{string,string,?string,?string}>
     */
    public function phrasesDataProvider(): array
    {
        return [
            'fixed plaintext and key(aes-128-cbc)' => [
                'test1234567',
                'aa4BtZ4tspm2wnXLb1ThQA==',
                null,
                'ILpoMowjIQjfYMR847rnFQ==',
            ],
            'empty plaintext with fixed key(aes-128-cbc)' => [
                '',
                'AAAAAAAAAAAAAAAAAAAAAA==',
                null,
                'AUPbY+5msM3/n2mRdoAVHg==',
            ],
            'fixed plaintext and random BASE62 key(aes-128-cbc)' => [
                'hello Alipay 你好 支付宝',
                base64_encode(Formatter::nonce(AesCbc::BLOCK_SIZE)),
                null,
                null,
            ],
            'empty text with random_bytes key(aes-128-cbc)' => [
                '',
                base64_encode(random_bytes(AesCbc::BLOCK_SIZE)),
                null,
                null,
            ],
            'fixed plaintext and key with iv(aes-128-cbc)' => [
                'test1234567',
                'aa4BtZ4tspm2wnXLb1ThQA==',
                'abcdef9876543210',
                'DDzIhdjWaYhIr8+I9ZGWkw==',
            ],
            'fixed plaintext and 24 bytes key(aes-192-cbc)' => [
                'test1234567',
                base64_encode('ABCDEF+/abcdef9876543210'),
                null,
                'UESum8UwtuviB/JcOzGbcw==',
            ],
            'fixed plaintext and 24 bytes key with iv(aes-192-cbc)' => [
                'test1234567',
                base64_encode('ABCDEF+/abcdef9876543210'),
                'abcdef9876543210',
                'wLy+9UkoDDVfVJfBygQPJA==',
            ],
            'fixed plaintext and 32 bytes key(aes-256-cbc)' => [
                'test1234567',
                base64_encode('abcdef9876543210abcdef9876543210'),
                null,
                'ysVQs71+fB2YcpLAncFy2g==',
            ],
            'fixed plaintext and 32 bytes key with iv(aes-256-cbc)' => [
                'test1234567',
                base64_encode('abcdef9876543210abcdef9876543210'),
                'abcdef9876543210',
                'VoxCvaXjQYnH59Uohld37g==',
            ],
        ];
    }

    /**
     * @dataProvider phrasesDataProvider
     * @param string $plaintext
     * @param string $cipherkey
     * @param ?string $iv
     * @param ?string $excepted
     */
    public function testEncrypt(string $plaintext, string $cipherkey, ?string $iv = null, ?string $excepted = null): void
    {
        $ciphertext = AesCbc::encrypt($plaintext, $cipherkey, $iv);
        self::assertIsString($ciphertext);
        self::assertNotEmpty($ciphertext);

        if (!is_null($excepted)) {
            self::assertEquals($ciphertext, $excepted);
        }

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $ciphertext);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $ciphertext);
        }
    }

    /**
     * @dataProvider phrasesDataProvider
     * @param string $plaintext
     * @param string $cipherkey
     * @param ?string $iv
     * @param ?string $ciphertext
     */
    public function testDecrypt(string $plaintext, string $cipherkey, ?string $iv = null, ?string $ciphertext = null): void
    {
        if (is_null($ciphertext)) {
            $ciphertext = AesCbc::encrypt($plaintext, $cipherkey, $iv);
        }

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(self::BASE64_EXPRESSION, $ciphertext);
        } else {
            self::assertRegExp(self::BASE64_EXPRESSION, $ciphertext);
        }

        self::assertIsString($ciphertext);
        self::assertNotEmpty($ciphertext);
        self::assertNotEquals($plaintext, $ciphertext);

        $excepted = AesCbc::decrypt($ciphertext, $cipherkey, $iv);

        self::assertIsString($excepted);
        self::assertEquals($plaintext, $excepted);
    }
}
