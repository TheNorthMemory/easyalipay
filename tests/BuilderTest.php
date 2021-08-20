<?php declare(strict_types=1);

namespace EasyAlipay\Tests;

use function array_map;
use function class_implements;
use function class_uses;
use function is_array;
use function iterator_to_array;
use function openssl_pkey_get_public;

use ArrayAccess;

use EasyAlipay\Builder;
use EasyAlipay\BuilderChainable;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/mock.%s.%s';

    public function testConstractor(): void
    {
        $this->expectError();
        new Builder(); /** @phpstan-ignore-line */
    }

    /**
     * @return array<string,array{string,mixed,mixed}>
     */
    public function configurationDataProvider(): array
    {

        $privateKey = openssl_pkey_get_private('file://' . sprintf(static::FIXTURES, 'pkcs8', 'key'));
        $publicKey  = openssl_pkey_get_public('file://' . sprintf(static::FIXTURES, 'spki', 'pem'));

        if (false === $privateKey || false === $publicKey) {
            throw new \Exception('Loading the pkey failed.');
        }

        return ['standard' => ['2014072300007148', $privateKey, $publicKey]];
    }

    /**
     * @dataProvider configurationDataProvider
     *
     * @param string $appId
     * @param mixed $privateKey
     * @param mixed $publicKey
     */
    public function testFactory(string $appId, $privateKey, $publicKey): void
    {
        $instance = Builder::factory([
            'privateKey' => $privateKey,
            'publicKey'  => $publicKey,
            'params'     => ['app_id' => $appId],
        ]);

        $map = class_implements($instance);

        self::assertIsArray($map);
        self::assertNotEmpty($map);

        self::assertArrayHasKey(BuilderChainable::class, is_array($map) ? $map : []);
        self::assertContainsEquals(BuilderChainable::class, is_array($map) ? $map : []);

        self::assertInstanceOf(ArrayAccess::class, $instance);
        self::assertInstanceOf(BuilderChainable::class, $instance);

        $traits = class_uses($instance);

        self::assertIsArray($traits);
        self::assertNotEmpty($traits);
        self::assertContains(\EasyAlipay\BuilderTrait::class, is_array($traits) ? $traits : []);

        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance->alipay);
        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance->alipay->system->oauth->token);
        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance->AlipaySystemOauthToken);
        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance->Alipay->Open->App->Qrcode->Create);

        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance['AlipayOpenAppQrcodeCreate']);
        /** @phpstan-ignore-next-line */
        self::assertInstanceOf(BuilderChainable::class, $instance['alipay.open.app.qrcode.create']);

        self::assertInstanceOf(BuilderChainable::class, $instance->chain('alipay.offline.material.image.upload'));

        /** @phpstan-ignore-next-line */
        $copy = iterator_to_array($instance->alipay->offline->material->image->upload);
        self::assertIsArray($copy);
        self::assertNotEmpty($copy);

        $context = $this;
        array_map(static function($item) use($context) {
            static::assertIsString($item);
            if (method_exists($context, 'assertMatchesRegularExpression')) {
                $context->assertMatchesRegularExpression('#[^A-Z]#', $item);
            } else {
                static::assertRegExp('#[^A-Z]#', $item);
            }
        }, $copy);
    }
}
