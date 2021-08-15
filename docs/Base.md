## 获取授权访问令牌和用户user_id

实例化

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

$appId = '2014072300007148';
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');

$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
    ],
]);

$textParams = [
    'grant_type' => 'authorization_code',
    'code' => $code,
];
```

### 同步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.system.oauth.token')
->get(['query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

属性式链规范

```php
$res = $instance
->alipay->system->oauth->token
->post(['query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

简链规范

```php
$res = $instance
->AlipaySystemOauthToken
->get($textParams, []);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.system.oauth.token')
->getAsync(['query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

属性式链规范

```php
$res = $instance
->alipay->system->oauth->token
->postAsync(['query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

简链规范

```php
$res = $instance
->AlipaySystemOauthToken
->getAsync($textParams, [])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 刷新授权访问令牌

实例化

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

$appId = '2014072300007148';
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');

$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
    ],
]);

$textParams = [
    'grant_type' => 'refresh_token',
    'refresh_token' => $refresh_token,
];
```

### 同步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.system.oauth.token')
->get(['query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

属性式链规范

```php
$res = $instance
->alipay->system->oauth->token
->post(['query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

简链规范

```php
$res = $instance
->AlipaySystemOauthToken
->get($textParams, []);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.system.oauth.token')
->getAsync(['query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

属性式链规范

```php
$res = $instance
->alipay->system->oauth->token
->postAsync(['query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

简链规范

```php
$res = $instance
->AlipaySystemOauthToken
->getAsync($textParams, [])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 创建小程序二维码

实例化

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;

$appId = '2014072300007148';
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');

$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
    ],
]);

$bizContent = [
    'url_param' => $query_param,
    'query_param' => $query_param,
    'describe' => $describe,
];
```
### 同步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.open.app.qrcode.create')
->get(['content' => $bizContent]);
print_r(json_decode((string)$res->getBody(), true));
```

属性式链规范

```php
$res = $instance
->alipay->open->app->qrcode->create
->post(['content' => $bizContent]);
print_r(json_decode((string)$res->getBody(), true));
```

简链规范

```php
$res = $instance
->AlipayOpenAppQrcodeCreate
->post($bizContent, []);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.open.app.qrcode.create')
->getAsync(['content' => $bizContent])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

属性式链规范

```php
$res = $instance
->alipay->open->app->qrcode->create
->getAsync(['content' => $bizContent])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

简链规范

```php
$res = $instance
->AlipayOpenAppQrcodeCreate
->postAsync($bizContent, [])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 上传门店照片

实例化

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;
use GuzzleHttp\Psr\MultipartStream;

$appId = '2014072300007148';
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');

$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
    ],
]);

$textParams = [
    'image_type' => 'jpg',
    'image_name' => $image_name,
];
$media = new MultipartStream([
    'name'     => 'image_content',
    'contents' => 'file:///path/for/uploading.jpg',
]);
```

### 同步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.offline.material.image.upload')
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

属性式链规范

```php
$res = $instance
->alipay->offline->material->image->upload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

简链规范

```php
$res = $instance
->AlipayOfflineMaterialImageUpload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.offline.material.image.upload')
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

属性式链规范

```php
$res = $instance
->alipay->offline->material->image->upload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

简链规范

```php
$res = $instance
->AlipayOfflineMaterialImageUpload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 上传门店视频

实例化

```php
use EasyAlipay\Builder;
use EasyAlipay\Crypto\Rsa;
use GuzzleHttp\Psr\MultipartStream;

$appId = '2014072300007148';
$privateKey = Rsa::fromPkcs1('MIIEpAIBAAKCAQEApdXuft3as2x...');
$publicKey = Rsa::fromSpki('MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCg...');

$instance = Builder::factory([
    'privateKey' => $privateKey,
    'publicKey' => $publicKey,
    'params' => [
        'app_id' => $appId,
    ],
]);

$textParams = [
    'image_type' => 'mp4',
    'image_name' => $image_name,
];
$media = new MultipartStream([
    'name'     => 'image_content',
    'contents' => 'file:///path/for/uploading.mp4',
]);
```

### 同步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.offline.material.image.upload')
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

属性式链规范

```php
$res = $instance
->alipay->offline->material->image->upload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

简链规范

```php
$res = $instance
->AlipayOfflineMaterialImageUpload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

内置`chain`规范

```php
$res = $instance
->chain('alipay.offline.material.image.upload')
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

属性式链规范

```php
$res = $instance
->alipay->offline->material->image->upload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

简链规范

```php
$res = $instance
->AlipayOfflineMaterialImageUpload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```
