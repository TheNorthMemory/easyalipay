## 统一收单交易创建接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
out_trade_no|String|必选|64|商户订单号。由商家自定义，64个字符以内，仅支持字母、数字、下划线且需保证在商户端不重复。|20150320010101001
total_amount|Price|必选|9|订单总金额。单位为元，精确到小数点后两位，取值范围：[0.01,100000000] 。|88.88
subject|String|必选|256|订单标题。注意：不可使用特殊字符，如 /，=，& 等。|Iphone6 16G
product_code|String|可选|64|产品码。商家和支付宝签约的产品码。 枚举值（点击查看签约情况）：FACE_TO_FACE_PAYMENT：当面付产品；默认值为FACE_TO_FACE_PAYMENT。|FACE_TO_FACE_PAYMENT
seller_id|String|可选|28|卖家支付宝用户ID。当需要指定收款账号时，通过该参数传入，如果该值为空，则默认为商户签约账号对应的支付宝用户ID。收款账号优先级规则：门店绑定的收款账户>请求传入的seller_id>商户签约账号对应的支付宝用户ID；注：直付通和机构间联场景下seller_id无需传入或者保持跟pid一致；如果传入的seller_id与pid不一致，需要联系支付宝小二配置收款关系；|2088102146225135
buyer_id|String|特殊可选|28|买家支付宝用户ID。2088开头的16位纯数字，小程序场景下获取用户ID请参考：用户授权;其它场景下获取用户ID请参考：网页授权获取用户信息;注：交易的买家与卖家不能相同。|2088102146225135
body|String|可选|128|订单附加信息。如果请求时传递了该参数，将在异步通知、对账单中原样返回，同时会在商户和用户的pc账单详情中作为交易描述展示|Iphone6 16G
goods_detail|array{object}|可选||订单包含的商品列表信息，json格式。
` `goods_id|String|必填|32|商品的编号|apple-01
` `goods_name|String|必填|256|商品名称|ipad
` `quantity|Number|必填|10|商品数量|1
` `price|Price|必填|9|商品单价，单位为元|2000
` `goods_category|String|可选|24|商品类目|34543238
` `categories_tree|String|可选|128|商品类目树，从商品类目根节点到叶子节点的类目id组成，类目id值使用|分割|124868003|126232002|126252004
` `show_url|String|可选|400|商品的展示地址|http://www.alipay.com/xxx.jpg
time_expire|String|可选|32|订单绝对超时时间。格式为yyyy-MM-dd HH:mm:ss。注：time_expire和timeout_express两者只需传入一个或者都不传，如果两者都传，优先使用time_expire。|2021-12-31 10:05:00
timeout_express|String|可选|6|订单相对超时时间。从交易创建时间开始计算。该笔订单允许的最晚付款时间，逾期将关闭交易。取值范围：1m～15d。m-分钟，h-小时，d-天，1c-当天（1c-当天的情况下，无论交易何时创建，都在0点关闭）。 该参数数值不接受小数点， 如 1.5h，可转换为 90m。当面付场景默认值为3h。注：time_expire和timeout_express两者只需传入一个或者都不传，如果两者都传，优先使用time_expire。|90m
settle_info|object|可选||描述结算信息，json格式。|
` `settle_detail_infos|array{object}|必填|10|结算详细信息，json数组，目前只支持一条。|
` ` ` `trans_in_type|String|必填|64|结算收款方的账户类型。cardAliasNo：结算收款方的银行卡编号;userId：表示是支付宝账号对应的支付宝唯一用户号;loginName：表示是支付宝登录号；defaultSettle：表示结算到商户进件时设置的默认结算账号，结算主体为门店时不支持传defaultSettle；|cardAliasNo
` ` ` `trans_in|String|必填|64|结算收款方。当结算收款方类型是cardAliasNo时，本参数为用户在支付宝绑定的卡编号；结算收款方类型是userId时，本参数为用户的支付宝账号对应的支付宝唯一用户号，以2088开头的纯16位数字；当结算收款方类型是loginName时，本参数为用户的支付宝登录号；当结算收款方类型是defaultSettle时，本参数不能传值，保持为空。|A0001
` ` ` `summary_dimension|String|可选|64|结算汇总维度，按照这个维度汇总成批次结算，由商户指定。目前需要和结算收款方账户类型为cardAliasNo配合使用|A0001
` ` ` `settle_entity_id|String|可选|64|结算主体标识。当结算主体类型为SecondMerchant时，为二级商户的SecondMerchantID；当结算主体类型为Store时，为门店的外标。|2088xxxxx;ST_0001
` ` ` `settle_entity_type|String|可选|32|结算主体类型。二级商户:SecondMerchant;商户或者直连商户门店:Store|SecondMerchant、Store
` ` ` `amount|Price|必填|9|结算的金额，单位为元。在创建订单和支付接口时必须和交易金额相同。在结算确认接口时必须等于交易金额减去已退款金额。|0.1
` `settle_period_time|String|可选|10|该笔订单的超期自动确认结算时间，到达期限后，将自动确认结算。此字段只在签约账期结算模式时有效。取值范围：1d～365d。d-天。 该参数数值不接受小数点。|7d
extend_params|object|可选||业务扩展参数|
` `sys_service_provider_id|String|可选|64|系统商编号该参数作为系统商返佣数据提取的依据，请填写系统商签约协议的PID|2088511833207846
` `card_type|String|可选|32|卡类型|S0JP0000
` `specified_seller_name|String|可选|32|特殊场景下，允许商户指定交易展示的卖家名称|XXX的跨境小铺
business_params|object|可选||商户传入业务信息，具体值要和支付宝约定，应用于安全，营销等参数直传场景，格式为json格式|{"data":"123"}
` `campus_card|String|可选|64|校园卡编号|0000306634
` `card_type|String|可选|128|虚拟卡卡类型|T0HK0000
` `actual_order_time|String|可选|256|实际订单时间，在乘车码场景，传入的是用户刷码乘车时间|2019-05-14 09:18:55
` `good_taxes|String|可选|32|商户传入的交易税费。需要落地风控使用|10.00
discountable_amount|Price|可选|9|可打折金额。参与优惠计算的金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]。如果同时传入了【可打折金额】、【不可打折金额】和【订单总金额】，则必须满足如下条件：【订单总金额】=【可打折金额】+【不可打折金额】。如果订单金额全部参与优惠计算，则【可打折金额】和【不可打折金额】都无需传入。|80.00
undiscountable_amount|Price|可选|9|不可打折金额。不参与优惠计算的金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]。如果同时传入了【可打折金额】、【不可打折金额】和【订单总金额】，则必须满足如下条件：【订单总金额】=【可打折金额】+【不可打折金额】。如果订单金额全部参与优惠计算，则【可打折金额】和【不可打折金额】都无需传入。|8.88
store_id|String|可选|32|商户门店编号。指商户创建门店时输入的门店编号。|NJ_001
operator_id|String|可选|28|商户操作员编号。|Yx_001
terminal_id|String|可选|32|商户机具终端编号。|NJ_T_001
logistics_detail|object|可选||物流信息|
` `logistics_type|String|可选|32|物流类型,POST 平邮,EXPRESS 其他快递,VIRTUAL 虚拟物品,EMS EMS,DIRECT 无需物流。|EXPRESS
receiver_address_info|object|可选||收货人及地址信息|
` `name|String|可选|512|收货人的姓名|张三
` `address|String|可选|512|收货地址|上海市浦东新区陆家嘴银城中路501号
` `mobile|String|可选|60|收货人手机号|13120180615
` `zip|String|可选|40|收货地址邮编|200120
` `division_code|String|可选|16|中国标准城市区域码|310115

### 同步模式

```php
$res = $instance
->alipay->trade->create
->post(['content' => [
    'out_trade_no' => '20150320010101001',
    'total_amount' => 88.88,
    'subject'      => 'Iphone6 16G',
    'product_code' => 'FACE_TO_FACE_PAYMENT',
    'seller_id'    => '2088102146225135',
    'buyer_id'     => '2088102146225135',
    'body'         => 'Iphone6 16G',
    'goods_detail' => [[
        'goods_id'        => 'apple-01',
        'goods_name'      => 'ipad',
        'quantity'        => 1,
        'price'           => 2000,
        'goods_category'  => '34543238',
        'categories_tree' => '124868003|126232002|126252004',
        'show_url'        => 'http://www.alipay.com/xxx.jpg',
    ],],
    'time_expire'     => '2021-12-31 10:05:00',
    'timeout_express' => '90m',
    'settle_info'     => [
        'settle_detail_infos' => [[
            'trans_in_type'      => 'cardAliasNo',
            'trans_in'           => 'A0001',
            'summary_dimension'  => 'A0001',
            'settle_entity_id'   => '2088xxxxx;ST_0001',
            'settle_entity_type' => 'SecondMerchant、Store',
            'amount'             => 0.1,
        ],],
        'trans_in_type'      => 'cardAliasNo',
        'trans_in'           => 'A0001',
        'summary_dimension'  => 'A0001',
        'settle_entity_id'   => '2088xxxxx;ST_0001',
        'settle_entity_type' => 'SecondMerchant、Store',
        'amount'             => 0.1,
        'settle_period_time' => '7d',
    ],
    'extend_params' => [
        'sys_service_provider_id' => '2088511833207846',
        'card_type'               => 'S0JP0000',
        'specified_seller_name'   => 'XXX的跨境小铺',
    ],
    'business_params' => [
        'campus_card'       => '0000306634',
        'card_type'         => 'T0HK0000',
        'actual_order_time' => '2019-05-14 09:18:55',
        'good_taxes'        => '10.00',
    ],
    'discountable_amount'   => 80,
    'undiscountable_amount' => 8.88,
    'store_id'              => 'NJ_001',
    'operator_id'           => 'Yx_001',
    'terminal_id'           => 'NJ_T_001',
    'logistics_detail'      => [
        'logistics_type' => 'EXPRESS',
    ],
    'receiver_address_info' => [
        'name'          => '张三',
        'address'       => '上海市浦东新区陆家嘴银城中路501号',
        'mobile'        => '13120180615',
        'zip'           => '200120',
        'division_code' => '310115',
    ],
], 'query' => [
    'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
],]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->trade->create
->postAsync(['content' => [
    'out_trade_no' => '20150320010101001',
    'total_amount' => 88.88,
    'subject'      => 'Iphone6 16G',
    'product_code' => 'FACE_TO_FACE_PAYMENT',
    'seller_id'    => '2088102146225135',
    'buyer_id'     => '2088102146225135',
    'body'         => 'Iphone6 16G',
    'goods_detail' => [[
        'goods_id'        => 'apple-01',
        'goods_name'      => 'ipad',
        'quantity'        => 1,
        'price'           => 2000,
        'goods_category'  => '34543238',
        'categories_tree' => '124868003|126232002|126252004',
        'show_url'        => 'http://www.alipay.com/xxx.jpg',
    ],],
    'time_expire'     => '2021-12-31 10:05:00',
    'timeout_express' => '90m',
    'settle_info'     => [
        'settle_detail_infos' => [[
            'trans_in_type'      => 'cardAliasNo',
            'trans_in'           => 'A0001',
            'summary_dimension'  => 'A0001',
            'settle_entity_id'   => '2088xxxxx;ST_0001',
            'settle_entity_type' => 'SecondMerchant、Store',
            'amount'             => 0.1,
        ],],
        'trans_in_type'      => 'cardAliasNo',
        'trans_in'           => 'A0001',
        'summary_dimension'  => 'A0001',
        'settle_entity_id'   => '2088xxxxx;ST_0001',
        'settle_entity_type' => 'SecondMerchant、Store',
        'amount'             => 0.1,
        'settle_period_time' => '7d',
    ],
    'extend_params' => [
        'sys_service_provider_id' => '2088511833207846',
        'card_type'               => 'S0JP0000',
        'specified_seller_name'   => 'XXX的跨境小铺',
    ],
    'business_params' => [
        'campus_card'       => '0000306634',
        'card_type'         => 'T0HK0000',
        'actual_order_time' => '2019-05-14 09:18:55',
        'good_taxes'        => '10.00',
    ],
    'discountable_amount'   => 80,
    'undiscountable_amount' => 8.88,
    'store_id'              => 'NJ_001',
    'operator_id'           => 'Yx_001',
    'terminal_id'           => 'NJ_T_001',
    'logistics_detail'      => [
        'logistics_type' => 'EXPRESS',
    ],
    'receiver_address_info' => [
        'name'          => '张三',
        'address'       => '上海市浦东新区陆家嘴银城中路501号',
        'mobile'        => '13120180615',
        'zip'           => '200120',
        'division_code' => '310115',
    ],
], 'query' => [
    'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
],])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```
