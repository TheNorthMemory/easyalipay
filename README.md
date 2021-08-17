# 支付宝 Alipay OpenAPI SDK

[A]Sync Chainable Alipay OpenAPI SDK for PHP

[![GitHub actions](https://github.com/TheNorthMemory/easyalipay/workflows/CI/badge.svg)](https://github.com/TheNorthMemory/easyalipay/actions)
[![Version](https://img.shields.io/packagist/v/easyalipay/easyalipay)](https://packagist.org/packages/easyalipay/easyalipay)
[![PHP Version](https://img.shields.io/packagist/php-v/easyalipay/easyalipay)](https://packagist.org/packages/easyalipay/easyalipay)
[![License](https://img.shields.io/packagist/l/easyalipay/easyalipay)](https://packagist.org/packages/easyalipay/easyalipay)

## 概览

支付宝 OpenAPI 的[Guzzle HttpClient](http://docs.guzzlephp.org/)封装组合，
内置 `请求签名` 和 `应答验签` 两个middlewares中间件，创新性地实现了链式面向对象同步/异步调用远程接口。

如果你是使用 `Guzzle` 的商户开发者，可以使用 `EasyAlipay\Builder::factory` 工厂方法直接创建一个 `GuzzleHttp\Client` 的链式调用封装器，
实例在执行请求时将自动携带身份认证信息，并检查应答的支付宝的返回签名。

## 环境要求

我们开发和测试使用的环境如下：

+ PHP >=7.2
+ guzzlehttp/guzzle ^7.0

**注:** 随`Guzzle7`支持的PHP版本最低为`7.2.5`，`PHP`小于这个版本的请选择其他优秀SDK；另PHP官方已于`30 Nov 2020`停止维护`PHP7.2`，详见附注链接。

## 安装

推荐使用PHP包管理工具`composer`引入SDK到项目中：

### 方式一

在项目目录中，通过composer命令行添加：

```shell
composer require easyalipay/easyalipay
```

### 方式二

在项目的`composer.json`中加入以下配置：

```json
"require": {
    "easyalipay/easyalipay": "^0.1"
}
```

添加配置后，执行安装

```shell
composer install
```

## 约定

本类库是以 `OpenAPI` `公共请求参数`中的接入方法 `method` 以`.`做切分，映射成`attributes`，编码书写方式有如下约定：

1. 请求 接入方法 `method` 切分后的每个`attributes`，可直接以对象获取形式串接，例如 `alipay.trade.query` 即串成 `alipay->trade->query`;
2. 每个 接入方法 `method` 所支持的 `HTTP METHOD`，即作为被串接对象的末尾执行方法，例如: `alipay->trade->query->post(['content' => []])`;
3. 每个 接入方法 `method` 所支持的 `HTTP METHOD`，同时支持`Async`语法糖，例如: `alipay->trade->query->postAsync(['content' => []])`;
4. 每个 接入方法 `method` 可以使用驼峰`PascalCase`风格书写，例如: `alipay.trade.query`可写成 `AlipayTradeQuery`;
5. 在IDE集成环境下，也可以按照内置的`chain($method)`接口规范，直接以接入方法 `method`作为变量入参，来获取`OpenAPI`当前接入方法的实例，驱动末尾执行方法(填入对应参数)，发起请求，例如 `chain('alipay.trade.query')->post(['content' => []])`；

以下示例用法，以`异步(Async/PromiseA+)`或`同步(Sync)`结合此种编码模式展开。

## 开始

首先，通过 `EasyAlipay\Builder::factory` 工厂方法构建一个实例，然后如上述`约定`，链式`同步`或`异步`请求远端`OpenAPI`接口。

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

//应用app_id
$appId = '2014072300007148';

//商户RSA私钥，入参是'从官方工具获取到的BASE64字符串'
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
// 以上是下列代码的语法糖，格式为 'private.pkcs1://' + '从官方工具获取到的字符串'
// $privateKey = Rsa::from('private.pkcs1://MIIEpAIBAAKCAQEApdXuft3as2x...');
// 也支持以下方式，须保证`private_key.pem`为完整X509格式
// $privateKey = Rsa::from('file:///your/openapi/private_key.pem');

//支付宝RSA公钥，入参是'从官方工具获取到的BASE64字符串'
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');
// 以上是下列代码的语法糖，格式为 'public.spki://' + '从官方工具获取到的字符串'
// $publicKey = Rsa::from('public.spki://MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');
// 也支持以下方式，须保证`public_key.pem`为完整X509格式
// $publicKey = Rsa::from('file:///the/alipay/public_key.pem');

//如果是公钥证书模式，可以在工厂方法内传入 `$appCertSn` 及 `$alipayRootCertSn`
// $appCertFilePath = '/my/cert/app_cert.crt';
// $appCertSn = \EasyAlipay\Helpers::sn($appCertFilePath);
// $alipayRootCertFilePath = '/alipay/cert/alipayRootCert.crt';
// $alipayRootCertSn = \EasyAlipay\Helpers::sn($alipayRootCertFilePath);

// 工厂方法构造一个实例
$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
        // 'app_auth_token' => $appAuthToken,
        // 'app_cert_sn' => $appCertSn,
        // 'alipay_root_cert_sn' => $alipayRootCertSn,
    ],
]);
```

初始化字典说明如下：

- `privateKey` 为`商户API私钥`，一般是通过官方证书生成工具生成字符串，支持`PKCS#1`及`PKCS#8`格式的私钥加载；
- `publicKey` 为`平台API公钥`，一般是通过官方证书生成工具生成字符串，支持`PKCS#8`及`SPKI`格式的公钥加载；
- `params` 接口中的`公共请求参数`配置项，已内置`charset=UTF-8`, `format=JSON`, `sign_type=RSA2`及`version=1.0`；
- `params['app_id' => $appId]` 为你的`应用app_id`；
- `params['app_auth_token' => $appAuthToken]` 为你的`ISV`模式的授权`token`，按需配置；
- `params['app_cert_sn' => $appCertSn]` 为`公钥证书模式`的商户证书相关信息`SN`，按需配置；
- `params['alipay_root_cert_sn' => $alipayRootCertSn]` 为`公钥证书模式`的平台证书相关信息`SN`，按需配置；

**注：** `OpenAPI` 以及 `GuzzleHttp\Client` 的 `array $config` 初始化参数，均融合在一个型参上。

### 统一收单线下交易查询

```php
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\RequestException;

try {
    $res = $instance
    ->alipay->trade->query
    ->get(['content' => [
        'out_trade_no' => '20150320010101001',
    ]]);

    echo $res->getBody(), PHP_EOL;
} catch (RequestException $e) {
    // 进行错误处理
    if ($e->hasResponse()) {
        $r = $e->getResponse();
        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
    }
} catch (\Throwable $e) {
    // 进行错误处理
    echo $e->getMessage(), PHP_EOL;
    echo $e->getTraceAsString(), PHP_EOL;
}
```

### 统一收单交易支付接口

```php
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

$res = $instance
->alipay->trade->pay
->postAsync([
    'out_trade_no' => '20150320010101001',
    'scene'        => 'bar_code',
    'auth_code'    => '28763443825664394',
    'product_code' => 'FACE_TO_FACE_PAYMENT',
    'subject'      => 'Iphone6 16G',
    'total_amount' => '88.88',
])
->then(static function(ResponseInterface $response) {
    // 正常逻辑回调处理
    return Utils::jsonDecode((string) $response->getBody(), true);
})
->otherwise(static function($e) {
    // 异常错误处理
    echo $e->getMessage(), PHP_EOL;
    if ($e instanceof RequestException && $e->hasResponse()) {
        $r = $e->getResponse();
        echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
        echo $r->getBody(), PHP_EOL, PHP_EOL, PHP_EOL;
    }
    echo $e->getTraceAsString(), PHP_EOL;
})
->wait();
print_r($res);
```

### 统一收单线下交易预创建

```php
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

$res = $instance
->Alipay->Trade->Precreate
->postAsync([
    'out_trade_no' => '20150320010101001',
    'subject'      => 'Iphone6 16G',
    'total_amount' => '88.88',
], ['query' => [
    'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm'
]])
->then(static function(ResponseInterface $response) {
    // 正常逻辑回调处理
    return Utils::jsonDecode((string) $response->getBody(), true);
})
->otherwise(static function($e) {
    // 异常错误处理
})
->wait();
print_r($res);
```

### 手机网站支付接口2.0

```php
use Psr\Http\Message\ResponseInterface;

$res = $instance
->chain('alipay.trade.wap.pay')
->postAsync([
    'subject'      => '商品名称',
    'out_trade_no' => '22',
    'total_amount' => '0.01',
    'product_code' => 'FAST_INSTANT_TRADE_PAY',
    'quit_url'     => 'https://forum.alipay.com/mini-app/post/15501011',
], ['pager' => true])
->then(static function(ResponseInterface $response) {
    // 正常逻辑回调处理
    return (string) $response->getBody();
})
->otherwise(static function($e) {
    // 异常错误处理
})
->wait();
print_r($res);
```

### 统一收单下单并支付页面接口

```php
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\RequestException;

try {
    $res = $instance['alipay.trade.page.pay']
    ->post([
        'subject'      => '商品名称',
        'out_trade_no' => '22',
        'total_amount' => '0.01',
        'product_code' => 'FAST_INSTANT_TRADE_PAY',
    ]);
    echo $resp->getBody(), PHP_EOL;
} catch (RequestException $e) {
    // 进行错误处理
} catch (\Throwable $e) {
    // 异常错误处理
}
```

### 上传门店照片和视频接口

```php
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr\MultipartStream;
use Psr\Http\Message\ResponseInterface;

$media = new MultipartStream([
    'name'     => 'image_content',
    'contents' => 'file:///path/for/uploading.jpg',
]);

$res = $instance
->chain('alipay.offline.material.image.upload')
->postAsync([
    'body' => $media,
])
->then(static function(ResponseInterface $response) {
    // 正常逻辑回调处理
    return Utils::jsonDecode((string) $response->getBody(), true);
})
->otherwise(static function($e) {
    // 异常错误处理
})
->wait();
print_r($res);
```

### 敏感信息加/解密

```php
use EasyAlipay\Crypto\AesCbc;
use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;

$aesCipherKey = '';

$res = $instance
->chain('some.method.response.by.aes.encrypted')
->postAsync([])
->then(static function(ResponseInterface $response) use ($aesCipherKey) {
    $json = Utils::jsonDecode((string) $response->getBody());
    return AesCbc::decrypt((string) $json->response, $aesCipherKey);
})
->wait();
print_r($res);
```

## 链接

- [更多示例代码](./docs/README.md)
- [GuzzleHttp官方版本支持](https://docs.guzzlephp.org/en/stable/overview.html#requirements)
- [PHP官方版本支持](https://www.php.net/supported-versions.php)

## 许可证

[MIT](LICENSE)
