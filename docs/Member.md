## 身份认证初始化服务

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
outer_order_no|string|必选|32|商户请求的唯一标识，商户要保证其唯一性，值为32位长度的字母数字组合。建议：前面几位字符是商户自定义的简称，中间可以使用一段时间，后段可以使用一个随机或递增序列|ZGYD201809132323000001234
biz_code|string|必选|32|认证场景码。入参支持的认证场景码和商户签约的认证场景相关，取值如下: FACE：多因子人脸认证 CERT_PHOTO：多因子证照认证 CERT_PHOTO_FACE ：多因子证照和人脸认证 SMART_FACE：多因子快捷认证|FACE
identity_param|object|必选|4096|需要验证的身份信息(json)字段说明如下：|
` `identity_type|string|必选||身份信息参数类型，固定为 CERT_INFO|CERT_INFO
` `cert_name|string|必选||真实姓名|收委
` `cert_no|string|必填||证件号码|260104197909275964
` `phone_no|string|选填||手机号码|13000000000
` `cert_type|string|必填||证件类型 必填，枚举支持：IDENTITY_CARD：身份证 HOME_VISIT_PERMIT_HK_MC：港澳通行证 HOME_VISIT_PERMIT_TAIWAN：台湾通行证 RESIDENCE_PERMIT_HK_MC：港澳居住证 RESIDENCE_PERMIT_TAIWAN：台湾居住证|IDENTITY_CARD
merchant_config|object|必选|4096|商户个性化配置，格式为json，详细支持的字段说明为：|
` `return_url|string|必填||需要回跳的目标地址，必填，一般指定为商户业务页面
` `face_reserve_strategy|string|选填||人脸保存策略，非必填；reserve(保存活体人脸)/never(不保存活体人脸)，不传默认为reserve|
face_contrast_picture|string|可选|1048576|自定义人脸比对图片的base64编码格式的string字符串|xydasf==


### 同步模式

```php
$res = $instance
->alipay->user->certifyOpenInitialize
->post(['content' => [
    'outer_order_no' => 'ZGYD201809132323000001234',
    'biz_code' => 'FACE',
    'identity_param' => [
        'identity_type' => 'CERT_INFO',
        'cert_type'     => 'IDENTITY_CARD',
        'cert_name'     => '收委',
        'cert_no'       => '260104197909275964',
        'phone_no'      => '13000000000',
    ],
    'merchant_config' => [
        'return_url' => 'xxx',
    ],
    'face_contrast_picture' => 'xydasf==',
],]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->user->certifyOpenInitialize
->postAsync(['content' => [
    'outer_order_no' => 'ZGYD201809132323000001234',
    'biz_code' => 'FACE',
    'identity_param' => [
        'identity_type' => 'CERT_INFO',
        'cert_type'     => 'IDENTITY_CARD',
        'cert_name'     => '收委',
        'cert_no'       => '260104197909275964',
        'phone_no'      => '13000000000',
    ],
    'merchant_config' => [
        'return_url' => 'xxx',
    ],
    'face_contrast_picture' => 'xydasf==',
],])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 身份认证开始认证

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
certify_id|String|必选|32|本次申请操作的唯一标识，由开放认证初始化接口调用后生成，后续的操作都需要用到|OC201809253000000393900404029253

### 同步模式

```php
$res = $instance
->alipay->user->certifyOpenCertify
->post(['content' => [
    'certify_id' => 'OC201809253000000393900404029253',
],]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->user->certifyOpenCertify
->postAsync(['content' => [
    'certify_id' => 'OC201809253000000393900404029253',
],])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 身份认证记录查询

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
certify_id|String|必选|32|本次申请操作的唯一标识，通过alipay.user.certify.open.initialize(身份认证初始化服务)接口同步响应获取。|OC201809253000000393900404029253

### 同步模式

```php
$res = $instance
->alipay->user->certifyOpenQeury
->post(['content' => [
    'certify_id' => 'OC201809253000000393900404029253',
],]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->user->certifyOpenQeury
->postAsync(['content' => [
    'certify_id' => 'OC201809253000000393900404029253',
],])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```
