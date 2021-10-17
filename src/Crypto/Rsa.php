<?php declare(strict_types=1);

namespace EasyAlipay\Crypto;

use const PHP_URL_SCHEME;

use function array_column;
use function array_combine;
use function array_keys;
use function base64_decode;
use function base64_encode;
use function gettype;
use function is_array;
use function is_bool;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;
use function ltrim;
use function openssl_pkey_get_private;
use function openssl_pkey_get_public;
use function openssl_sign;
use function openssl_verify;
use function pack;
use function parse_url;
use function preg_match;
use function sprintf;
use function str_replace;
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

    /** asymmetric public key type string */
    public const KEY_TYPE_PUBLIC = 'public';
    /** asymmetric private key type string */
    public const KEY_TYPE_PRIVATE = 'private';

    /** @var array{'RSA':'sha1WithRSAEncryption','RSA2':'sha256WithRSAEncryption'} */
    private const ALGOES = ['RSA' => 'sha1WithRSAEncryption', 'RSA2' => 'sha256WithRSAEncryption'];

    private const LOCAL_FILE_PROTOCOL = 'file://';
    private const PKEY_PEM_NEEDLE = ' KEY-';
    private const PKEY_PEM_FORMAT = "-----BEGIN %1\$s KEY-----\n%2\$s\n-----END %1\$s KEY-----";
    private const PKEY_PEM_FORMAT_PATTERN = '#-{5}BEGIN ((?:RSA )?(?:PUBLIC|PRIVATE)) KEY-{5}\r?\n([^-]+)\r?\n-{5}END \1 KEY-{5}#';
    private const CHR_CR = "\r";
    private const CHR_LF = "\n";

    /** @var array<string,array{string,string,int}> - Supported loading rules */
    private const RULES = [
        'private.pkcs1' => [self::PKEY_PEM_FORMAT, 'RSA PRIVATE', 16],
        'private.pkcs8' => [self::PKEY_PEM_FORMAT, 'PRIVATE',     16],
        'public.pkcs1'  => [self::PKEY_PEM_FORMAT, 'RSA PUBLIC',  15],
        'public.spki'   => [self::PKEY_PEM_FORMAT, 'PUBLIC',      14],
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
        $pkey = openssl_pkey_get_private(static::parse(sprintf('private.pkcs8://%s', $thing)));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the PKCS#8 privateKey(%s).', $thing));
        }

        return $pkey;
    }

    /**
     * Sugar for loading input `privateKey/publicKey` string, pure `base64-encoded-string` without LF and evelope.
     *
     * Kind of the \$type Boolean is deprecated, use `self::KEY_TYPE_PRIVATE` or `self::KEY_TYPE_PUBLIC` instead.
     *
     * @param string $thing - The string in `PKCS#1` format.
     * @param boolean|string $type - Either `self::KEY_TYPE_PUBLIC` or `self::KEY_TYPE_PRIVATE` string, default is `self::KEY_TYPE_PRIVATE`.
     * @return \OpenSSLAsymmetricKey|resource|mixed
     * @throws UnexpectedValueException
     */
    public static function fromPkcs1(string $thing, $type = self::KEY_TYPE_PRIVATE)
    {
        $pkey = ($isPublic = is_bool($type) ? $type : $type === static::KEY_TYPE_PUBLIC)
            ? openssl_pkey_get_public(static::parse(sprintf('public.pkcs1://%s', $thing), $type))
            : openssl_pkey_get_private(static::parse(sprintf('private.pkcs1://%s', $thing)));

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
        $pkey = openssl_pkey_get_public(static::parse(sprintf('public.spki://%s', $thing), static::KEY_TYPE_PUBLIC));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf('Cannot load the SPKI publicKey(%s).', $thing));
        }

        return $pkey;
    }

    /**
     * Loading the privateKey/publicKey from a protocol like string.
     *
     * The `\$thing` can be one of the following:
     * - `file://` protocol `PKCS#1/PKCS#8 privateKey`/`SPKI publicKey`/`x509 certificate(for publicKey)` string.
     * - `public.spki://`, `public.pkcs1://`, `private.pkcs1://`, `private.pkcs8://` protocols string.
     * - full `PEM` in `PKCS#1/PKCS#8` format `privateKey`/`publicKey`/`x509 certificate(for publicKey)` string.
     * - `\OpenSSLAsymmetricKey` (PHP8) or `resource#pkey` (PHP7).
     * - `\OpenSSLCertificate` (PHP8) or `resource#X509` (PHP7) for publicKey.
     * - `Array` of `[privateKeyString,passphrase]` for encrypted privateKey.
     *
     * Kind of the \$type Boolean is deprecated, use `self::KEY_TYPE_PRIVATE` or `self::KEY_TYPE_PUBLIC` instead.
     *
     * @param \OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|array{string,string}|string|mixed $thing - The string.
     * @param boolean|string $type - Either `self::KEY_TYPE_PUBLIC` or `self::KEY_TYPE_PRIVATE` string, default is `self::KEY_TYPE_PRIVATE`.
     *
     * @return \OpenSSLAsymmetricKey|resource|mixed
     */
    public static function from($thing, $type = self::KEY_TYPE_PRIVATE)
    {
        $pkey = ($isPublic = is_bool($type) ? $type : $type === static::KEY_TYPE_PUBLIC)
            ? openssl_pkey_get_public(static::parse($thing, $type))
            : openssl_pkey_get_private(static::parse($thing));

        if (false === $pkey) {
            throw new UnexpectedValueException(sprintf(
                'Cannot load %s from(%s).',
                $isPublic ? 'publicKey' : 'privateKey',
                is_string($thing) ? $thing : gettype($thing)
            ));
        }

        return $pkey;
    }

    /**
     * Parse the `\$thing` for the `openssl_pkey_get_public`/`openssl_pkey_get_private` function.
     *
     * The `\$thing` can be the `file://` protocol privateKey/publicKey string, eg:
     *   - `file:///my/path/to/private.pkcs1.key`
     *   - `file:///my/path/to/private.pkcs8.key`
     *   - `file:///my/path/to/public.spki.pem`
     *   - `file:///my/path/to/x509.crt` (for publicKey)
     *
     * The `\$thing` can be the `public.spki://`, `public.pkcs1://`, `private.pkcs1://`, `private.pkcs8://` protocols string, eg:
     *   - `public.spki://MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...`
     *   - `public.pkcs1://MIIBCgKCAQEAgYxTW5Yj...`
     *   - `private.pkcs1://MIIEpAIBAAKCAQEApdXuft3as2x...`
     *   - `private.pkcs8://MIIEpAIBAAKCAQEApdXuft3as2x...`
     *
     * The `\$thing` can be the string with PEM `evelope`, eg:
     *   - `-----BEGIN RSA PRIVATE KEY-----...-----END RSA PRIVATE KEY-----`
     *   - `-----BEGIN PRIVATE KEY-----...-----END PRIVATE KEY-----`
     *   - `-----BEGIN RSA PUBLIC KEY-----...-----END RSA PUBLIC KEY-----`
     *   - `-----BEGIN PUBLIC KEY-----...-----END PUBLIC KEY-----`
     *   - `-----BEGIN CERTIFICATE-----...-----END CERTIFICATE-----` (for publicKey)
     *
     * The `\$thing` can be the \OpenSSLAsymmetricKey/\OpenSSLCertificate/resouce, eg:
     *   - `\OpenSSLAsymmetricKey` (PHP8) or `resource#pkey` (PHP7) for publicKey/privateKey.
     *   - `\OpenSSLCertificate` (PHP8) or `resource#X509` (PHP7) for publicKey.
     *
     * The `\$thing` can be the Array{$privateKey,$passphrase} style for loading privateKey, eg:
     *   - [`file:///my/path/to/encrypted.private.pkcs8.key`, 'your_pass_phrase']
     *   - [`-----BEGIN ENCRYPTED PRIVATE KEY-----...-----END ENCRYPTED PRIVATE KEY-----`, 'your_pass_phrase']
     *
     * Kind of the \$type Boolean is deprecated, use `self::KEY_TYPE_PRIVATE` or `self::KEY_TYPE_PUBLIC` instead.
     *
     * @param \OpenSSLAsymmetricKey|\OpenSSLCertificate|resource|array{string,string}|string|mixed $thing - The thing.
     * @param boolean|string $type - Either `self::KEY_TYPE_PUBLIC` or `self::KEY_TYPE_PRIVATE` string, default is `self::KEY_TYPE_PRIVATE`.
     * @return \OpenSSLAsymmetricKey|resource|array{string,string}|string|mixed
     */
    private static function parse($thing, $type = self::KEY_TYPE_PRIVATE)
    {
        $src = $thing;

        if (is_resource($src) || is_object($src) || is_array($src) || is_int(strpos($src, self::LOCAL_FILE_PROTOCOL))) {
            return $src;
        }

        if (is_int(strpos($src, '://'))) {
            $protocol = parse_url($src, PHP_URL_SCHEME);
            [$format, $kind, $offset] = static::RULES[$protocol] ?? [null, null, null];
            if ($format && $kind && $offset) {
                $src = substr($src, $offset);
                if ('public.pkcs1' === $protocol) {
                    $src = static::pkcs1ToSpki($src);
                    [$format, $kind] = static::RULES['public.spki'];
                }
                return sprintf($format, $kind, wordwrap($src, 64, self::CHR_LF, true));
            }
        }

        if (is_int(strpos($src, self::PKEY_PEM_NEEDLE))) {
            if (((is_bool($type) && $type) || $type === self::KEY_TYPE_PUBLIC) && preg_match(self::PKEY_PEM_FORMAT_PATTERN, $src, $matches)) {
                [, $kind, $base64] = $matches;
                $mapRules = (array)array_combine(array_column(self::RULES, 1/*column*/), array_keys(self::RULES));
                $protocol = $mapRules[$kind] ?? '';
                if ('public.pkcs1' === $protocol) {
                    return self::parse(sprintf('%s://%s', $protocol, str_replace([self::CHR_CR, self::CHR_LF], '', $base64)), $type);
                }
            }
            return $src;
        }

        return $src;
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
