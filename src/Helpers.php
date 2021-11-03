<?php declare(strict_types=1);

namespace EasyAlipay;

use const PHP_EOL;

use function array_filter;
use function array_keys;
use function array_reduce;
use function array_reverse;
use function array_walk;
use function file_get_contents;
use function hash_final;
use function hash_init;
use function hash_update;
use function implode;
use function openssl_x509_parse;
use function preg_match_all;
use function stripos;

/**
 * Provide some useful functions for the catificate(s) operations.
 */
class Helpers
{
    private const ALGO_MD5 = 'md5';

    private const X509_CERT_FORMAT = '#(?<cert>-----BEGIN CERTIFICATE-----(?:[^-]+)-----END CERTIFICATE-----)#';
    private const X509_ASN1_CERT_SIGNATURE_LONG_NAME = 'signatureTypeLN';
    private const X509_ASN1_CERT_ISSUER = 'issuer';
    private const X509_ASN1_CERT_SERIAL = 'serialNumber';

    /** @var string `pem` identify */
    public const CERT_PEM = 'pem';

    /** @var string `attr` identify for the result of the `openssl_x509_parse` */
    public const CERT_ATTR = 'attr';

    /**
     * MD5 hash function
     *
     * @param string $things - To caculating things
     *
     * @return string - The digest string
     */
    public static function md5(string ...$things): string
    {
        $ctx = hash_init(self::ALGO_MD5);

        array_walk($things, static function(string $thing) use ($ctx): void { hash_update($ctx, $thing); });

        return hash_final($ctx);
    }

    /**
     * Load Rsa X509 Certificate(s).
     *
     * @param string $thing - The certificatie(s) file path string or `data://text/plain;utf-8,...` (RFC2397) string
     * @param string $pattern - The signatureAlgorithm matching pattern, default is `null` means for all
     *
     * @return array<array{'pem':string,'attr':array<mixed>}> - The X509 Certificate instance list.
     */
    public static function load(string $thing, ?string $pattern = null): array
    {
        preg_match_all(self::X509_CERT_FORMAT, file_get_contents($thing) ?: '', $matches);

        $certs = $matches['cert'] ?? [];

        array_walk($certs, static function(string &$cert) use ($pattern): void {
            $attr = openssl_x509_parse($cert, true);
            [self::X509_ASN1_CERT_SIGNATURE_LONG_NAME => $algo] = $attr ?: [self::X509_ASN1_CERT_SIGNATURE_LONG_NAME => null];
            $cert = $pattern && $algo && false === stripos($algo, $pattern) ? null : [self::CERT_PEM => $cert, self::CERT_ATTR => $attr];
        });

        /** @var array<array{'pem':string,'attr':array<mixed>}> $certs */
        return array_filter($certs);
    }

    /**
     * Extract a certificate from given `thing`
     *
     * @param string $thing - The certificatie(s) file path string or `data://text/plain;utf-8,...` (RFC2397) string
     * @param string $pattern - The signatureAlgorithm matching pattern, default is `null` means for all
     *
     * @return string - The pem format certificate(s)
     */
    public static function extract(string $thing, ?string $pattern = null): string
    {
        return implode(PHP_EOL, array_reduce(static::load($thing, $pattern), static function(array $carry, array $cert): array {
            $carry[] = $cert[static::CERT_PEM];

            return $carry;
        }, []));
    }

    /**
     * Calculate the given certificate(s) `SN` value string, rule as `md5(CN=$CN,OU=$OU,O=$O,C=$C$serialNumber)`
     *
     * @param string $thing - The certificatie(s) file path string or `data://text/plain;utf-8,...` (RFC2397) string
     * @param string $pattern - The signatureAlgorithm matching pattern, default is `null` means for all
     *
     * @return string - The SN value string
     */
    public static function sn(string $thing, ?string $pattern = null): string
    {
        return implode('_', array_reduce(static::load($thing, $pattern), static function(array $carry, array $cert): array {
            /**
             * @var array<string,string> $issuer
             * @var string $serial
             */
            [self::X509_ASN1_CERT_ISSUER => $issuer, self::X509_ASN1_CERT_SERIAL => $serial] = $cert[self::CERT_ATTR];

            $carry[] = static::md5(static::fold($issuer), $serial);

            return $carry;
        }, []));
    }

    /**
     * Folder the `key/value` \$things in a reversed order then joined as `,`
     *
     * @param array<string,string> $things - The `issuer` or `subject` arrtributes array
     */
    public static function fold(array $things): string
    {
        return implode(',', array_reverse(array_reduce(
            array_keys($things),
            static function(array $carry, string $key) use($things): array {
                $carry[] = implode('=', [$key, $things[$key]]);

                return $carry;
            },
            []
        )));
    }
}
