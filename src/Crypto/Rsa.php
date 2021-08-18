<?php declare(strict_types=1);

namespace EasyAlipay\Crypto;

use const PHP_URL_SCHEME;

use function base64_decode;
use function base64_encode;
use function ltrim;
use function openssl_pkey_get_private;
use function openssl_pkey_get_public;
use function openssl_sign;
use function openssl_verify;
use function pack;
use function parse_url;
use function sprintf;
use function strlen;
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
        'public.pkcs1'  => [self::PKEY_FORMAT, 'RSA PUBLIC',  15],
        'public.spki'   => [self::PKEY_FORMAT, 'PUBLIC',      14],
    ];

    /**
     * @var string - Equal to `sequence(oid(1.2.840.113549.1.1.1), null))`
     * @link https://datatracker.ietf.org/doc/html/rfc3447#appendix-A.2
     */
    private const ASN1_OID_RSAENCRYPTION = '300d06092a864886f70d0101010500';
    private const ASN1_SEQUENCE = 48;
    private const CHR_NUL = "\0";
    private const CHR_ETX = "\3";

    /**
     * Translate the \$thing strlen from `X690` style to the `ASN.1` 128bit hexadecimal length string
     *
     * @param string $thing - The string
     *
     * @return string The `ASN.1` 128bit hexadecimal length string
     */
    private static function encodeLength(string $thing): string
    {
        $num = strlen($thing);
        if ($num <= 0x7F) {
            return sprintf('%c', $num);
        }

        $tmp = ltrim(pack('N', $num), self::CHR_NUL);
        return pack('Ca*', strlen($tmp) | 0x80, $tmp);
    }

    /**
     * Convert the `PKCS#1` format RSA Public Key to `SPKI` format
     *
     * @param string $thing - The base64-encoded string, without evelope style
     *
     * @return string The `SPKI` style public key without evelope string
     */
    public static function pkcs1ToSpki(string $thing): string
    {
        $raw = self::CHR_NUL . base64_decode($thing);
        $new = pack('H*', self::ASN1_OID_RSAENCRYPTION) . self::CHR_ETX . self::encodeLength($raw) . $raw;

        return base64_encode(pack('Ca*a*', self::ASN1_SEQUENCE, self::encodeLength($new), $new));
    }

    /**
     * Sugar for loading input `privateKey` string, pure `base64-encoded-string` without LF and evelope.
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
     * Sugar for loading input `privateKey/publicKey` string, pure `base64-encoded-string` without LF and evelope.
     *
     * @param string $thing - The string in `PKCS#1` format.
     * @param boolean $isPublic - The `$thing` is public key string.
     * @return \OpenSSLAsymmetricKey|resource|mixed
     * @throws UnexpectedValueException
     */
    public static function fromPkcs1(string $thing, bool $isPublic = false)
    {
        $pkey = $isPublic
            ? openssl_pkey_get_public(static::from(sprintf('public.pkcs1://%s', $thing)))
            : openssl_pkey_get_private(static::from(sprintf('private.pkcs1://%s', $thing)));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the PKCS#1 %s(%s).', $isPublic ? 'publicKey' : 'privateKey', $thing));
        }

        return $pkey;
    }

    /**
     * Sugar for loading input `publicKey` string, pure `base64-encoded-string` without LF and evelope.
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
            $src = substr($thing, $offset);
            if ('public.pkcs1' === $protocol) {
                $src = static::pkcs1ToSpki($src);
                [, $kind] = static::RULES['public.spki'];
            }
            return sprintf($format, $kind, wordwrap($src, 64, "\n", true));
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
