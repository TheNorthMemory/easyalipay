<?php declare(strict_types=1);

namespace EasyAlipay;

use const ENT_COMPAT;
use const PREG_UNMATCHED_AS_NULL;
use const SORT_FLAG_CASE;
use const SORT_NATURAL;

use function array_keys;
use function array_map;
use function array_reduce;
use function count;
use function htmlspecialchars;
use function implode;
use function is_null;
use function ksort;
use function ord;
use function preg_match;
use function random_bytes;
use function sprintf;
use function str_split;
use function time;
use function vsprintf;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Provides easy used methods using in this project.
 */
class Formatter
{
    /**
     * Generate a random BASE62 string aka `nonce`, similar as `random_bytes`.
     *
     * @param int $size - Nonce string length, default is 32.
     *
     * @return string - base62 random string.
     */
    public static function nonce(int $size = 32): string
    {
        if ($size < 1) {
            throw new InvalidArgumentException('Size must be a positive integer.');
        }

        return implode('', array_map(static function(string $c): string {
            return '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'[ord($c) % 62];
        }, str_split(random_bytes($size))));
    }

    /**
     * Sort an array by key with `SORT_FLAG_CASE | SORT_NATURAL` flag.
     *
     * @param array<string, string|int> $thing - The input array.
     *
     * @return array<string, string|int> - The sorted array.
     */
    public static function ksort(array $thing = []): array
    {
        ksort($thing, SORT_FLAG_CASE | SORT_NATURAL);

        return $thing;
    }

    /**
     * Like `queryString` does but without the `sign` and `empty value` entities.
     *
     * @param array<string, string|int|null> $thing - The input array.
     *
     * @return string - The `key=value` pair string whose joined by `&` char.
     */
    public static function queryStringLike(array $thing = []): string
    {
        $data = [];

        foreach ($thing as $key => $value) {
            if ($key === 'sign' || is_null($value) || $value === '') {
                continue;
            }
            $data[] = implode('=', [$key, $value]);
        }

        return implode('&', $data);
    }

    /**
     * Retrieve the current `yyyy-MM-dd HH:mm:ss` date time based on given `timeZone`.
     *
     * @param string $when - Any available inputs refer to the `DateTime() constructor`, default `Date.now()`.
     * @param string $timeZone - Any available inputs refer to the options in `DateTimeZone`, default `Asia/Shanghai`.
     *
     * @return string - `yyyy-MM-dd HH:mm:ss` date time string
     */
    public static function localeDateTime(string $when = 'now', string $timeZone = 'Asia/Shanghai'): string
    {
        return (new DateTime($when, new DateTimeZone($timeZone)))->format('Y-m-d H:i:s');
    }

    /**
     * Parse the `source` with given `placeholder`.
     *
     * @param string $source - The inputs string.
     * @param string $placeholder - The payload pattern.
     *
     * @return array{ident:?string,payload:?string,sign:?string}
     */
    public static function fromJsonLike(string $source, string $placeholder = '(?<ident>[a-z](?:[a-z_])+)_response'): array
    {
        $maybe = '(?:[\r|\n|\s|\t]*)';
        $pattern = "#^{$maybe}\{{$maybe}\"{$placeholder}\"{$maybe}:{$maybe}\"?(?<payload>.*?)\"?{$maybe}"
                 . "(?:,)?{$maybe}(?:\"sign\"{$maybe}:{$maybe}\"(?<sign>[^\"]+)\"{$maybe})?\}{$maybe}$#m";

        preg_match($pattern, $source, $matches, PREG_UNMATCHED_AS_NULL);

        return ['ident' => $matches['ident'] ?? null, 'payload' => $matches['payload'] ?? null, 'sign' => $matches['sign'] ?? null];
    }

    /**
     * flat the `key/value` \$inputs as html `<input/>` tag list
     *
     * @param string $template - The `sprintf` string template, acceptable `key` and `value` as parameters
     * @param array<string,string> $inputs - The `key/value` pair
     * @return string[]
     */
    protected static function inputsFlat(string $template, array $inputs): array
    {
        return array_reduce(
            array_keys($inputs),
            static function(array $carry, string $key) use ($template, $inputs): array {
                $carry[] = sprintf($template, htmlspecialchars($key, ENT_COMPAT), htmlspecialchars($inputs[$key], ENT_COMPAT));
                return $carry;
            },
            []
        );
    }

    /**
     * Translate the inputs for the page service, such as `alipay.trade.page.pay`, `alipay.trade.wap.pay` OpenAPI methods.
     *
     * @param string $baseUri - The gateway base_uri
     * @param string $method - The http verb, one of `GET` or `POST`
     * @param array<string,string> $query - The http query
     * @param array<string,string> $data - The additional data
     */
    public static function page(string $baseUri = '', string $method = '', array $query = [], array $data = []): string
    {
        $name = 'EasyAlipay' . time();

        ['charset' => $charset] = $query;
        unset($query['charset']);

        return implode('', ['<!DOCTYPE html>',
            '<html lang="zh-CN">',
            '<head>',
            '<title>...</title>',
            sprintf('<meta http-equiv="Content-Type" content="text/html; charset=%s"/>', $charset),
            '</head>',
            '<body>',
            sprintf('<form id="%1$s" name="%1$s" method="%2$s" action="%3$s?charset=%4$s">', $name, $method, $baseUri, $charset),
            vsprintf(str_repeat('%s', count($in = static::inputsFlat('<input type="hidden" name="%s" value="%s"/>', $query))), $in),
            vsprintf(str_repeat('%s', count($in = static::inputsFlat('<input type="hidden" name="%s" value="%s"/>', $data))), $in),
            '</form>',
            sprintf('<script>function %1$s(){document.%1$s.submit()}try{document.addEventListener(\'DOMContentLoaded\',%1$s,false)}catch(e){%1$s()}</script>', $name),
            sprintf('<noscript>Your browser doesn\'t support javascript, please click <button form="%s" type="submit" accesskey="s">Submit</button> to continue.</noscript>', $name),
            '</body>',
            '</html>',
        ]);
    }
}
