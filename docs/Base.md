## 获取授权访问令牌和用户user_id

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
grant_type|String|必选|20|授权方式。支持：1.authorization_code，表示换取使用用户授权码code换取授权令牌access_token。 |authorization_code
code|String|可选|40|授权码，用户对应用授权后得到。本参数在 grant_type 为 authorization_code 时必填。|4b203fe6c11548bcabd8da5bb087a83b

### 同步模式

```php
$res = $instance
->alipay->system->oauthToken
->get(['query' => [
    'grant_type' => 'authorization_code',
    'code' => $code,
]]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->system->oauthToken
->getAsync(['query' => [
    'grant_type' => 'authorization_code',
    'code' => $code,
]])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 刷新授权访问令牌

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
grant_type|String|必选|20|授权方式。refresh_token，表示使用refresh_token刷新获取新授权令牌。|refresh_token
refresh_token|String|可选|40|刷新令牌，上次换取访问令牌时得到。本参数在 grant_type 为 authorization_code 时不填；为 refresh_token 时必填，且该值来源于此接口的返回值 app_refresh_token（即至少需要通过 grant_type=authorization_code 调用此接口一次才能获取）。|201208134b203fe6c11548bcabd8da5bb087

### 同步模式

```php
$res = $instance
->alipay->system->oauthToken
->post(['query' => [
    'grant_type' => 'refresh_token',
    'refresh_token' => $refresh_token,
]]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->system->oauthToken
->getAsync(['query' => [
    'grant_type' => 'refresh_token',
    'refresh_token' => $refresh_token,
]])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 创建小程序二维码

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
url_param|String|必选|256|page/component/component-pages/view/view为小程序中能访问到的页面路径|page/component/component-pages/view/view
query_param|String|必选|256|小程序的启动参数，打开小程序的query ，在小程序 onLaunch的方法中获取|x=1
describe|String|必选|32|对应的二维码描述|二维码描述

### 同步模式

```php
$res = $instance
->alipay->open->appQrcodeCreate
->post(['content' => [
    'url_param' => $query_param,
    'query_param' => $query_param,
    'describe' => $describe,
]]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->open->appQrcodeCreate
->postAsync(['content' => [
    'url_param' => $query_param,
    'query_param' => $query_param,
    'describe' => $describe,
]])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 上传门店照片

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
image_type|string|必选|8|图片格式|jpg
image_name|string|必选|128|图片/视频名称|海底捞
image_content|file|必选|5242880|图片/视频二进制内容，图片/视频大小不能超过5M|-

实例化

```php
use GuzzleHttp\Psr\MultipartStream;

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

```php
$res = $instance
->alipay->offline->materialImageUpload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->offline->materialImageUpload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 上传门店视频

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
image_type|string|必选|8|视频格式|mp4
image_name|string|必选|128|图片/视频名称|海底捞
image_content|file|必选|5242880|图片/视频二进制内容，图片/视频大小不能超过5M|-

实例化

```php
use GuzzleHttp\Psr\MultipartStream;

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

```php
$res = $instance
->alipay->offline->materialImageUpload
->post(['body' => $media, 'query' => $textParams]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->offline->materialImageUpload
->postAsync(['body' => $media, 'query' => $textParams])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```
