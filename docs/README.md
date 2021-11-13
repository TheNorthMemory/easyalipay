# 接口示例

## 实例化

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
```

## [基础能力](Base.md)

方法|属性链|类别|说明
---|---|---|---
`alipay.system.oauth.token`|`->alipay->system->oauthToken`|工具类|获取授权访问令牌和用户user_id
`alipay.system.oauth.token`|`->alipay->system->oauthToken`|工具类|刷新授权访问令牌
`alipay.open.app.qrcode.create`|`->alipay->open->appQrcodeCreate`|营销|小程序生成推广二维码接口
`alipay.offline.material.image.upload`|`->alipay->offline->materialImageUpload`|店铺|上传门店照片接口
`alipay.offline.material.image.upload`|`->alipay->offline->materialImageUpload`|店铺|上传门店视频接口
