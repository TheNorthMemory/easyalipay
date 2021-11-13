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

方法|属性链|说明
---|---|---
`alipay.system.oauth.token`|`->alipay->system->oauthToken`|获取授权访问令牌和用户user_id
`alipay.system.oauth.token`|`->alipay->system->oauthToken`|刷新授权访问令牌
`alipay.open.app.qrcode.create`|`->alipay->open->appQrcodeCreate`|营销|小程序生成推广二维码接口
`alipay.offline.material.image.upload`|`->alipay->offline->materialImageUpload`|上传门店照片接口
`alipay.offline.material.image.upload`|`->alipay->offline->materialImageUpload`|上传门店视频接口

## [会员](Member.md)

方法|属性链|说明
---|---|---
`alipay.user.certify.open.initialize`|`->alipay->user->certifyOpenInitialize`|身份认证初始化服务
`alipay.user.certify.open.certify`|`->alipay->user->certifyOpenCertify`|身份认证开始认证
`alipay.user.certify.open.query`|`->alipay->user->certifyOpenQuery`|身份认证记录查询

## [支付](Payment.md)

方法|属性链|说明
---|---|---
`alipay.trade.create`|`->alipay->trade->create`|统一收单交易创建接口
`alipay.trade.query`|`->alipay->trade->query`
`alipay.trade.refund`|`->alipay->trade->refund`
`alipay.trade.close`|`->alipay->trade->close`
`alipay.trade.fastpay.refund.query`|`->alipay->trade->fastpayRefundQuery`
`alipay.data.dataservice.bill.downloadurl.query`|`->alipay->data->dataserviceBillDownloadurlQuery`
`alipay.trade.create`|`->alipay->trade->create`
`alipay.trade.pay`|`->alipay->trade->pay`
`alipay.trade.precreate`|`->alipay->trade->precreate`
`alipay.trade.app.pay`|`->alipay->trade->appPay`
`alipay.trade.page.pay`|`->alipay->trade->pagePay`
`alipay.trade.wap.pay`|`->alipay->trade->wapPay`

## [安全](Security.md)

方法|属性链|说明
---|---|---
`alipay.security.risk.content.detect`|`->alipay->security->riskContentDetect`
## [营销](Marketing.md)

方法|属性链|说明
---|---|---
`alipay.pass.template.add`|`->alipay->passTemplateAdd`
`alipay.pass.template.Update`|`->alipay->passTemplateUpdate`
`alipay.pass.instance.add`|`->alipay->passInstanceAdd`
`alipay.pass.instance.update`|`->alipay->passInstanceUpdate`
`alipay.open.app.mini.templatemessage.send`|`->alipay->open->appMiniTemplatemessageSend`
`alipay.open.public.message.content.create`|`->alipay->open->publicMessageContentCreate`
`alipay.open.public.message.content.modify`|`->alipay->open->publicMessageContentModify`
`alipay.open.public.message.total.send`|`->alipay->open->publicMessageTotalSend`
`alipay.open.public.message.total.send`|`->alipay->open->publicMessageTotalSend`
`alipay.open.public.message.single.send`|`->alipay->open->publicMessageSingleSend`
`alipay.open.public.life.msg.recall`|`->alipay->open->publicLifeMsgRecall`
`alipay.open.public.template.message.industry.modify`|`->alipay->open->publicTemplateMessageIndustryModify`
`alipay.open.public.setting.category.query`|`->alipay->open->publicSettingCategoryQuery`
