<?php declare(strict_types=1);

namespace EasyAlipay\Crypto;

use const PHP_URL_SCHEME;

use function base64_decode;
use function base64_encode;
use function openssl_sign;
use function openssl_verify;
use function openssl_pkey_get_public;
use function parse_url;
use function sprintf;
use function substr;
use function wordwrap;

use UnexpectedValueException;

/**
 * Provides sign/verify for the RSA(`sha1WithRSAEncryption`)/RSA2(`sha256WithRSAEncryption`) cryptos.
 */
class Rsa
{
    /** @var string - Alias of the `sha1WithRSAEncryption` algothrim */
    public const ALGO_TYPE_RSA = 'RSA';

    /** @var string - Alias of the `sha256WithRSAEncryption` algothrim */
    public const ALGO_TYPE_RSA2 = 'RSA2';

    /** @var array{'RSA':'sha1WithRSAEncryption','RSA2':'sha256WithRSAEncryption'} */
    private const ALGOES = ['RSA' => 'sha1WithRSAEncryption', 'RSA2' => 'sha256WithRSAEncryption'];

    /** @var string */
    private const PKEY_FORMAT = "-----BEGIN %1\$s KEY-----\n%2\$s\n-----END %1\$s KEY-----";

    /** @var array<string,array{string,string,int}> - Supported loading rules */
    private const RULES = [
        'private.pkcs1' => [self::PKEY_FORMAT, 'RSA PRIVATE', 16],
        'private.pkcs8' => [self::PKEY_FORMAT, 'PRIVATE',     16],
        'public.spki'   => [self::PKEY_FORMAT, 'PUBLIC',      14],
    ];

    /**
     * Sugar for loading input `privateKey` string.
     *
     * @param string $thing - The string in `PKCS#8` format.
     * @return \OpenSSLAsymmetricKey|resource|mixed
     * @throws UnexpectedValueException
     */
    public static function fromPkcs8(string $thing)
    {
        $pkey = openssl_pkey_get_private(static::from(sprintf('private.pkcs8://%s', $thing)));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the PKCS#8 privateKey(%s).', $thing));
        }

        return $pkey;
    }

    /**
     * Sugar for loading input `privateKey` string, PHP doesnot supported PKCS#1's publicKey.
     *
     * @param string $thing - The string in `PKCS#1` format.
     * @return \OpenSSLAsymmetricKey|resource|mixed
     * @throws UnexpectedValueException
     */
    public static function fromPkcs1(string $thing)
    {
        $pkey = openssl_pkey_get_private(static::from(sprintf('private.pkcs1://%s', $thing)));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the PKCS#1 privateKey(%s).', $thing));
        }

        return $pkey;
    }

    /**
     * Sugar for loading input `publicKey` string.
     *
     * @param string $thing - The string in `SKPI` format.
     * @return \OpenSSLAsymmetricKey|resource|mixed
     * @throws UnexpectedValueException
     */
    public static function fromSpki(string $thing)
    {
        $pkey = openssl_pkey_get_public(static::from(sprintf('public.spki://%s', $thing)));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the SPKI publicKey(%s).', $thing));
        }

        return $pkey;
    }

    /**
     * Loading the privateKey/publicKey from a protocol like string.
     *
     * @param string $thing - The `private.pkcs1://` and `public.spki://` protocols string.
     *
     * @return string - The content can be passed onto `openssl_sign`(privateKey) or `openssl_verify`(publicKey).
     */
    public static function from(string $thing): string
    {
        $protocol = parse_url($thing, PHP_URL_SCHEME);
        [$format, $kind, $offset] = static::RULES[$protocol] ?? [null, null, null];

        if ($format && $kind && $offset) {
            return sprintf($format, $kind, wordwrap(substr($thing, $offset), 64, "\n", true));
        }

        return $thing;
    }

    /**
     * Verifying the `message` with given `signature` string that uses `RSA/RSA2` algothrim.
     *
     * @param string $message - Content will be `openssl_verify`.
     * @param string $signature - The base64-encoded ciphertext.
     * @param \OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|string|mixed $publicKey - A PEM encoded public key.
     * @param string $type - one of the algo alias RSA/RSA2, default is `RSA2`.
     *
     * @return boolean - True is passed, false is failed.
     * @throws UnexpectedValueException
     */
    public static function verify(string $message, string $signature, $publicKey, string $type = self::ALGO_TYPE_RSA2): bool
    {
        if (false === ($result = openssl_verify($message, base64_decode($signature), $publicKey, self::ALGOES[$type] ?? self::ALGOES[self::ALGO_TYPE_RSA2]))) {
            throw new UnexpectedValueException("Verified the {$message} by {$type} failed, please checking the \$publicKey whether or nor correct.");
        }

        return $result === 1;
    }

    /**
     * Creates and returns a `base64_encode` string that uses `RSA/RSA2` algothrim.
     *
     * @param string $message - Content will be `openssl_sign`.
     * @param \OpenSSLAsymmetricKey|resource|string|mixed $privateKey - A PEM encoded private key.
     * @param string $type - one of the algo alias RSA/RSA2, default is `RSA2`.
     *
     * @return string - The base64-encoded signature.
     * @throws UnexpectedValueException
     */
    public static function sign(string $message, $privateKey, string $type = self::ALGO_TYPE_RSA2): string
    {
        if (false === openssl_sign($message, $signature, $privateKey, self::ALGOES[$type] ?? self::ALGOES[self::ALGO_TYPE_RSA2])) {
            throw new UnexpectedValueException("Signing the {$message} by {$type} failed, please checking the \$privateKey whether or nor correct.");
        }

        return base64_encode($signature);
    }
}
