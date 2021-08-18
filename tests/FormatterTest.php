<?php declare(strict_types=1);

namespace EasyAlipay\Tests;

use function count;
use function implode;
use function is_array;
use function is_null;
use function is_string;
use function ksort;
use function method_exists;
use function strlen;

use InvalidArgumentException;

use EasyAlipay\Formatter;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    private const REGULAR_YMDHIS = '#^\d{4}-(?:0[0-9]|1[0-2])-(?:0[0-9]|[1-2][0-9]|3[01]) (?:[01][0-9]|2[0-4])(?::[0-5][0-9]){2}$#';

    /**
     * @return array<string,array{int,string}>
     */
    public function nonceRulesProvider(): array
    {
        return [
            'default $size=32'       => [32,  '#[a-zA-Z0-9]{32}#'],
            'half-default $size=16'  => [16,  '#[a-zA-Z0-9]{16}#'],
            'hundred $size=100'      => [100, '#[a-zA-Z0-9]{100}#'],
            'one $size=1'            => [1,   '#[a-zA-Z0-9]{1}#'],
            'zero $size=0'           => [0,   '#Size must be a positive integer\.#'],
            'negative $size=-1'      => [-1,  '#Size must be a positive integer\.#'],
            'negative $size=-16'     => [-16, '#Size must be a positive integer\.#'],
            'negative $size=-32'     => [-32, '#Size must be a positive integer\.#'],
        ];
    }

    /**
     * @dataProvider nonceRulesProvider
     */
    public function testNonce(int $size, string $pattern): void
    {
        if ($size < 1) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageMatches($pattern);
        }

        $nonce = Formatter::nonce($size);

        self::assertIsString($nonce);

        self::assertTrue(strlen($nonce) === $size);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $nonce);
        } else {
            self::assertRegExp($pattern, $nonce);
        }
    }

    /**
     * @return array<string,array<array<string,string>>>
     */
    public function ksortByFlagNaturePhrasesProvider(): array
    {
        return [
            'normal' => [
                ['a' => '1', 'b' => '3', 'aa' => '2'],
                ['a' => '1', 'aa' => '2', 'b' => '3'],
            ],
            'key with numeric' => [
                ['rfc1' => '1', 'b' => '4', 'rfc822' => '2', 'rfc2086' => '3'],
                ['b' => '4', 'rfc1' => '1', 'rfc822' => '2', 'rfc2086' => '3'],
            ],
        ];
    }

    /**
     * @param array<string,string> $thing
     * @param array<string,string> $excepted
     * @dataProvider ksortByFlagNaturePhrasesProvider
     */
    public function testKsort(array $thing, array $excepted): void
    {
        self::assertEquals(Formatter::ksort($thing), $excepted);
    }

    /**
     * @return array<string,array<array<string,string>>>
     */
    public function nativeKsortPhrasesProvider(): array
    {
        return [
            'normal' => [
                ['a' => '1', 'b' => '3', 'aa' => '2'],
                ['a' => '1', 'aa' => '2', 'b' => '3'],
            ],
            'key with numeric' => [
                ['rfc1' => '1', 'b' => '4', 'rfc822' => '2', 'rfc2086' => '3'],
                ['b' => '4', 'rfc1' => '1', 'rfc2086' => '3', 'rfc822' => '2'],
            ],
        ];
    }

    /**
     * @param array<string,string> $thing
     * @param array<string,string> $excepted
     * @dataProvider nativeKsortPhrasesProvider
     */
    public function testNativeKsort(array $thing, array $excepted): void
    {
        self::assertTrue(ksort($thing));
        self::assertEquals($thing, $excepted);
    }

    /**
     * @return array<string,array{array<string,string|null>,string}>
     */
    public function queryStringLikePhrasesProvider(): array
    {
        return [
            'none specific chars' => [
                ['a' => '1', 'b' => '3', 'aa' => '2'],
                'a=1&b=3&aa=2',
            ],
            'has `sign` key' => [
                ['a' => '1', 'b' => '3', 'sign' => '2'],
                'a=1&b=3',
            ],
            'has `empty` value' => [
                ['a' => '1', 'b' => '3', 'c' => ''],
                'a=1&b=3',
            ],
            'has `null` value' => [
                ['a' => '1', 'b' => null, 'c' => '2'],
                'a=1&c=2',
            ],
            'mixed `sign` key, `empty` and `null` values' => [
                ['bob' => '1', 'alice' => null, 'tom' => '', 'sign' => 'mock'],
                'bob=1',
            ],
        ];
    }

    /**
     * @param array<string,string|null> $thing
     * @param string $excepted
     * @dataProvider queryStringLikePhrasesProvider
     */
    public function testQueryStringLike(array $thing, string $excepted): void
    {
        $value = Formatter::queryStringLike($thing);
        self::assertIsString($value);
        self::assertEquals($value, $excepted);
    }

    /**
     * @return array<string,(string|null)[]>
     */
    public function localeDateTimePhrasesProvider(): array
    {
        return [
            'unix timestamp(`@1600538598`) string without timezone'           => ['2020-09-19 18:03:18', '@1600538598', null],
            'unix timestamp(`@1600538598`) string with `Asia/Shanghai`'       => ['2020-09-19 18:03:18', '@1600538598', 'Asia/Shanghai'],
            'unix timestamp(`@1600538598`) string with `America/Los_Angeles`' => ['2020-09-19 18:03:18', '@1600538598', 'America/Los_Angeles'],

            '`January 1, 1970, 00:00:00 UTC` without timezone'           => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00 UTC', null],
            '`January 1, 1970, 00:00:00 UTC` with `Asia/Shanghai`'       => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00 UTC', 'Asia/Shanghai'],
            '`January 1, 1970, 00:00:00 UTC` with `America/Los_Angeles`' => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00 UTC', 'America/Los_Angeles'],

            '`January 1, 1970, 00:00:00` without timezone'           => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00', null],
            '`January 1, 1970, 00:00:00` with `Asia/Shanghai`'       => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00', 'Asia/Shanghai'],
            '`January 1, 1970, 00:00:00` with `America/Los_Angeles`' => ['1970-01-01 00:00:00', 'January 1, 1970, 00:00:00', 'America/Los_Angeles'],

            '`1970-01-01 00:00:00` without timezone'           => ['1970-01-01 00:00:00', '1970-01-01 00:00:00', null],
            '`1970-01-01 00:00:00` with `Asia/Shanghai`'       => ['1970-01-01 00:00:00', '1970-01-01 00:00:00', 'Asia/Shanghai'],
            '`1970-01-01 00:00:00` with `America/Los_Angeles`' => ['1970-01-01 00:00:00', '1970-01-01 00:00:00', 'America/Los_Angeles'],

            '`Mon, 21 Sep 2020 04:00:00 GMT` without timezone'           => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00 GMT', null],
            '`Mon, 21 Sep 2020 04:00:00 GMT` with `Asia/Shanghai`'       => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00 GMT', 'Asia/Shanghai'],
            '`Mon, 21 Sep 2020 04:00:00 GMT` with `America/Los_Angeles`' => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00 GMT', 'America/Los_Angeles'],

            '`Mon, 21 Sep 2020 04:00:00` without timezone'           => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00', null],
            '`Mon, 21 Sep 2020 04:00:00` with `Asia/Shanghai`'       => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00', 'Asia/Shanghai'],
            '`Mon, 21 Sep 2020 04:00:00` with `America/Los_Angeles`' => ['2020-09-21 04:00:00', 'Mon, 21 Sep 2020 04:00:00', 'America/Los_Angeles'],

            '`2020-09-21 04:00:00` without timezone'           => ['2020-09-21 04:00:00', '2020-09-21 04:00:00', null],
            '`2020-09-21 04:00:00` with `Asia/Shanghai`'       => ['2020-09-21 04:00:00', '2020-09-21 04:00:00', 'Asia/Shanghai'],
            '`2020-09-21 04:00:00` with `America/Los_Angeles`' => ['2020-09-21 04:00:00', '2020-09-21 04:00:00', 'America/Los_Angeles'],

            '`2019-01-01` without timezone'           => ['2019-01-01 00:00:00', '2019-01-01', null],
            '`2019-01-01` with `Asia/Shanghai`'       => ['2019-01-01 00:00:00', '2019-01-01', 'Asia/Shanghai'],
            '`2019-01-01` with `America/Los_Angeles`' => ['2019-01-01 00:00:00', '2019-01-01', 'America/Los_Angeles'],

            '`08/03/2016 00:00:00` without timezone'           => ['2016-08-03 00:00:00', '08/03/2016 00:00:00', null],
            '`08/03/2016 00:00:00` with `Asia/Shanghai`'       => ['2016-08-03 00:00:00', '08/03/2016 00:00:00', 'Asia/Shanghai'],
            '`08/03/2016 00:00:00` with `America/Los_Angeles`' => ['2016-08-03 00:00:00', '08/03/2016 00:00:00', 'America/Los_Angeles'],
            '`08/03/2016 00:00:00` with `Europe/London`'       => ['2016-08-03 00:00:00', '08/03/2016 00:00:00', 'Europe/London'],

            '`first sat of July 2008` without timezone'           => ['2008-07-05 00:00:00', 'first sat of July 2008', null],
            '`first sat of July 2008` with `Asia/Shanghai`'       => ['2008-07-05 00:00:00', 'first sat of July 2008', 'Asia/Shanghai'],
            '`first sat of July 2008` with `America/Los_Angeles`' => ['2008-07-05 00:00:00', 'first sat of July 2008', 'America/Los_Angeles'],
            '`first sat of July 2008` with `Europe/London`'       => ['2008-07-05 00:00:00', 'first sat of July 2008', 'Europe/London'],

            '`last day of February 2012` without timezone'           => ['2012-02-29 00:00:00', 'last day of February 2012', null],
            '`last day of February 2012` with `Asia/Shanghai`'       => ['2012-02-29 00:00:00', 'last day of February 2012', 'Asia/Shanghai'],
            '`last day of February 2012` with `America/Los_Angeles`' => ['2012-02-29 00:00:00', 'last day of February 2012', 'America/Los_Angeles'],
            '`last day of February 2012` with `Europe/London`'       => ['2012-02-29 00:00:00', 'last day of February 2012', 'Europe/London'],
        ];
    }

    /**
     * @param string $excepted
     * @param string $when
     * @param string $timeZone
     * @dataProvider localeDateTimePhrasesProvider
     */
    public function testLocaleDateTime(string $excepted, string $when, ?string $timeZone = null): void
    {
        $datetime = is_null($timeZone) ? Formatter::localeDateTime($when) : Formatter::localeDateTime($when, $timeZone);

        self::assertIsString($datetime);

        self::assertTrue(strlen($datetime) === 19);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression(static::REGULAR_YMDHIS, $datetime);
        } else {
            self::assertRegExp(static::REGULAR_YMDHIS, $datetime);
        }

        self::assertEquals($excepted, $datetime);
    }

    /**
     * @return array<string,mixed>
     */
    public function fromJsonLikePhrasesProvider(): array
    {
        return [
            'empty string' => [
                ['ident' => null, 'payload' => null, 'sign' => null],
                '',
                null
            ],
            'escaped slash(/) with extra spaces string' => [
                ['ident' => 'ali_pay', 'payload' => 'https:\/\/alipay.com', 'sign' => 'MA=='],
                '{"ali_pay_response"  :  "https:\\/\\/alipay.com"  ,  "sign":"MA=="}',
                null
            ],
            'pretty and escaped slash(/) with extra spaces string' => [
                ['ident' => 'ali_pay', 'payload' => 'https:\/\/alipay.com', 'sign' => 'MA=='],
                implode('', [
                    $LF = "\n",
                    $TAB = "\t", '{"ali_pay_response"',
                    $LF, ':  "https:\\/\\/alipay.com"  ',
                    $TAB, $LF, ',  "sign"',
                    $TAB, $LF, ':"MA=="}',
                    $TAB, $LF]),
                null
            ],
            '`error_response` without `sign` key' => [
                ['ident' => 'error', 'payload' => 'isv.permission=no', 'sign' => null],
                '{"error_response":"isv.permission=no"}',
                null
            ],
            'bad JSON format `error_response` without `sign` key' => [
                ['ident' => 'error', 'payload' => '{"code":"40004","message":isv.permission=no"}', 'sign' => null],
                '{"error_response":{"code":"40004","message":isv.permission=no"},}',
                null
            ],
        ];
    }

    /**
     * @param array<string,?string> $excepted
     * @param string $json
     * @param ?string $placeholder
     * @dataProvider fromJsonLikePhrasesProvider
     */
    public function testFromJsonLike(array $excepted, string $json, ?string $placeholder = null): void
    {
        $things = is_null($placeholder) ? Formatter::fromJsonLike($json) : Formatter::fromJsonLike($json, $placeholder);

        self::assertIsArray($things);
        self::assertArrayHasKey('ident', $things);
        self::assertArrayHasKey('payload', $things);
        self::assertArrayHasKey('sign', $things);
        self::assertTrue(count($things) === 3);
        self::assertEquals($excepted, $things);
    }

    /**
     * @return array<string,mixed>
     */
    public function pagePhrasesProvider(): array
    {
        return [
            'no optional params in ' => [null, null, null, null],
            'test environments in ' => ['https://openapi.test.alipay.com/gateway.do', 'GET', ['charset' => 'GBK'], []],
        ];
    }

    /**
     * @param ?string $baseUri
     * @param ?string $method
     * @param ?array<string,string> $query
     * @param ?array<string,string> $data
     * @dataProvider pagePhrasesProvider
     */
    public function testPage(?string $baseUri = null, ?string $method = null, ?array $query = null, ?array $data = null): void
    {
        $html = is_string($baseUri) && is_string($method) && is_array($query) && is_array($data)
            ? Formatter::page($baseUri, $method, $query, $data)
            : Formatter::page();

        self::assertIsString($html);

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression('#<html[^>]+>.*?</html>#', $html);
            $this->assertMatchesRegularExpression('#<form[^>]+>.*?</form>#', $html);
            $this->assertMatchesRegularExpression('#<script>.*?</script>#', $html);
        } else {
            self::assertRegExp('#<html[^>]+>.*?</html>#', $html);
            self::assertRegExp('#<form[^>]+>.*?</form>#', $html);
            self::assertRegExp('#<script>.*?</script>#', $html);
        }
    }
}
