<?php declare(strict_types=1);

namespace EasyAlipay\Crypto;

use const OPENSSL_RAW_DATA;

use function base64_decode;
use function base64_encode;
use function openssl_decrypt;
use function openssl_encrypt;
use function sprintf;
use function str_repeat;
use function strlen;

use UnexpectedValueException;

/**
 * Aes encrypt/decrypt using `CBC MODE` algorithm with pkcs7padding.
 */
class AesCbc
{
    /** @var int - Bytes Length of the AES block */
    public const BLOCK_SIZE = 16;

    /** @var string - `cbc` mode pattern */
    private const MODE_CBC = 'aes-%d-cbc';

    /** @var string - `NULL` character */
    private const CHR_NUL = "\0";

    /**
     * Detect the `[algo,key,options,iv]` with given `cipherkey` and `iv`.
     *
     * @param string $cipherkey - The secret key, base64 encoded string.
     * @param ?string $iv - The initialization vector, 16 bytes string.
     *
     * @return array{string,string,int,string}
     */
    private static function detector(string $cipherkey, ?string $iv = null): array
    {
        /** @var string $key */
        $key = base64_decode($cipherkey);

        return [sprintf(static::MODE_CBC, strlen($key) * 8), $key, OPENSSL_RAW_DATA, $iv ?? str_repeat(static::CHR_NUL, static::BLOCK_SIZE)];
    }

    /**
     * Encrypts given data with given key and iv, returns a base64 encoded string.
     *
     * @param string $plaintext - Text to encode.
     * @param string $cipherkey - The secret key, base64 encoded string.
     * @param ?string $iv - The initialization vector, 16 bytes string.
     *
     * @return string - The base64-encoded ciphertext.
     * @throws UnexpectedValueException
     */
    public static function encrypt(string $plaintext, string $cipherkey, ?string $iv = null): string
    {
        $ciphertext = openssl_encrypt($plaintext, ...static::detector($cipherkey, $iv));

        if (false === $ciphertext) {
            throw new UnexpectedValueException("Encrypting the {$plaintext} failed, please checking the {$cipherkey} and {$iv} whether or nor correct.");
        }

        return base64_encode($ciphertext);
    }

    /**
     * Takes a base64 encoded string and decrypts it using a given key and iv.
     *
     * @param string $ciphertext - The base64-encoded ciphertext.
     * @param string $cipherkey - The secret key, base64 encoded string.
     * @param ?string $iv - The initialization vector, 16 bytes string.
     *
     * @return string - The utf-8 plaintext.
     * @throws UnexpectedValueException
     */
    public static function decrypt(string $ciphertext, string $cipherkey, ?string $iv = null): string
    {
        $plaintext = openssl_decrypt(base64_decode($ciphertext), ...static::detector($cipherkey, $iv));

        if (false === $plaintext) {
            throw new UnexpectedValueException("Decrypting the {$ciphertext} failed, please checking the {$cipherkey} and {$iv} whether or nor correct.");
        }

        return $plaintext;
    }
}
