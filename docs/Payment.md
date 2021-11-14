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

## 统一收单线下交易查询接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
out_trade_no|String|特殊可选|64|订单支付时传入的商户订单号,和支付宝交易号不能同时为空。trade_no,out_trade_no如果同时存在优先取trade_no|20150320010101001
trade_no|String|特殊可选|64|支付宝交易号，和商户订单号不能同时为空|2014112611001004680073956707
org_pid|String|可选|16|银行间联模式下有用，其它场景请不要使用；双联通过该参数指定需要查询的交易所属收单机构的pid;|2088101117952222
query_options|String[]|可选|1024|查询选项，商户传入该参数可定制本接口同步响应额外返回的信息字段，数组格式。支持枚举如下：trade_settle_info：返回的交易结算信息，包含分账、补差等信息。fund_bill_list：交易支付使用的资金渠道。|trade_settle_info

### 同步模式

```php
$res = $instance
->alipay->trade->query
->post([
    'content' => [
        'out_trade_no'  => '20150320010101001',
        'trade_no'      => '2014112611001004680073956707',
        'org_pid'       => '2088101117952222',
        'query_options' => ['trade_settle_info', 'fund_bill_list'],
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```

### 异步模式

```php
$res = $instance
->alipay->trade->query
->postAsync([
    'content' => [
        'out_trade_no'  => '20150320010101001',
        'trade_no'      => '2014112611001004680073956707',
        'org_pid'       => '2088101117952222',
        'query_options' => ['trade_settle_info', 'fund_bill_list'],
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 统一收单交易退款接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
out_trade_no|String|特殊可选|64|商户订单号。订单支付时传入的商户订单号，商家自定义且保证商家系统中唯一。与支付宝交易号 trade_no 不能同时为空。|20150320010101001
trade_no|String|特殊可选|64|支付宝交易号。和商户订单号 out_trade_no 不能同时为空。|2014112611001004680073956707
refund_amount|Price|必选|11|退款金额。需要退款的金额，该金额不能大于订单金额，单位为元，支持两位小数。注：如果正向交易使用了营销，该退款金额包含营销金额，支付宝会按业务规则分配营销和买家自有资金分别退多少，默认优先退买家的自有资金。如交易总金额100元，用户使用了80元自有资金和20元营销券，则全额退款时应该传入的退款金额是100元。|200.12
refund_reason|String|可选|256|退款原因说明。商家自定义，将在对账单的退款明细中作为备注返回，同时会在商户和用户的pc退款账单详情中展示|正常退款
out_request_no|String|可选|64|退款请求号。标识一次退款请求，需要保证在交易号下唯一，如需部分退款，则此参数必传。注：针对同一次退款请求，如果调用接口失败或异常了，重试时需要保证退款请求号不能变更，防止该笔交易重复退款。支付宝会保证同样的退款请求号多次请求只会退一次。|HZ01RF001
refund_royalty_parameters|array{object}|可选||退分账明细信息。注： 1.当面付且非直付通模式无需传入退分账明细，系统自动按退款金额与订单金额的比率，从收款方和分账收入方退款，不支持指定退款金额与退款方。2.直付通模式，电脑网站支付，手机 APP 支付，手机网站支付产品，须在退款请求中明确是否退分账，从哪个分账收入方退，退多少分账金额；如不明确，默认从收款方退款，收款方余额不足退款失败。不支持系统按比率退款。|
` `royalty_type|String|可选|32|分账类型.普通分账为：transfer;补差为：replenish;为空默认为分账transfer;|transfer
` `trans_out|String|可选|16|支出方账户。如果支出方账户类型为userId，本参数为支出方的支付宝账号对应的支付宝唯一用户号，以2088开头的纯16位数字；如果支出方类型为loginName，本参数为支出方的支付宝登录号。 泛金融类商户分账时，该字段不要上送。|2088101126765726
` `trans_out_type|String|可选|64|支出方账户类型。userId表示是支付宝账号对应的支付宝唯一用户号;loginName表示是支付宝登录号； 泛金融类商户分账时，该字段不要上送。|userId
` `trans_in_type|String|可选|64|收入方账户类型。userId表示是支付宝账号对应的支付宝唯一用户号;cardAliasNo表示是卡编号;loginName表示是支付宝登录号；|userId
` `trans_in|String|必填|16|收入方账户。如果收入方账户类型为userId，本参数为收入方的支付宝账号对应的支付宝唯一用户号，以2088开头的纯16位数字；如果收入方类型为cardAliasNo，本参数为收入方在支付宝绑定的卡编号；如果收入方类型为loginName，本参数为收入方的支付宝登录号；|2088101126708402
` `amount|Price|可选|9|分账的金额，单位为元|0.1
` `desc|String|可选|1000|分账描述|分账给2088101126708402
` `royalty_scene|String|可选|256|可选值：达人佣金、平台服务费、技术服务费、其他|达人佣金
query_options|String[]|可选|1024|查询选项。商户通过上送该参数来定制同步需要额外返回的信息字段，数组格式。支持：refund_detail_item_list：退款使用的资金渠道。|refund_detail_item_list


### 同步模式

```php
$res = $instance
->alipay->trade->refund
->post([
    'content' => [
        'out_trade_no'   => '20150320010101001',
        'trade_no'       => '2014112611001004680073956707',
        'refund_amount'  => 200.12,
        'refund_reason'  => '正常退款',
        'out_request_no' => 'HZ01RF001',
        'refund_royalty_parameters' => [[
            'royalty_type'   => 'transfer',
            'trans_out'      => '2088101126765726',
            'trans_out_type' => 'userId',
            'trans_in_type'  => 'userId',
            'trans_in'       => '2088101126708402',
            'amount'         => 0.1,
            'desc'           => '分账给2088101126708402',
            'royalty_scene'  => '达人佣金',
        ],],
        'query_options' => ['trade_settle_info', 'fund_bill_list'],
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->trade->refund
->postAsync([
    'content' => [
        'out_trade_no'   => '20150320010101001',
        'trade_no'       => '2014112611001004680073956707',
        'refund_amount'  => 200.12,
        'refund_reason'  => '正常退款',
        'out_request_no' => 'HZ01RF001',
        'refund_royalty_parameters' => [[
            'royalty_type'   => 'transfer',
            'trans_out'      => '2088101126765726',
            'trans_out_type' => 'userId',
            'trans_in_type'  => 'userId',
            'trans_in'       => '2088101126708402',
            'amount'         => 0.1,
            'desc'           => '分账给2088101126708402',
            'royalty_scene'  => '达人佣金',
        ],],
        'query_options' => ['trade_settle_info', 'fund_bill_list'],
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 统一收单交易关闭接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
trade_no|String|特殊可选|64|该交易在支付宝系统中的交易流水号。最短 16 位，最长 64 位。和out_trade_no不能同时为空，如果同时传了 out_trade_no和 trade_no，则以 trade_no为准。|2013112611001004680073956707
out_trade_no|String|特殊可选|64|订单支付时传入的商户订单号,和支付宝交易号不能同时为空。 trade_no,out_trade_no如果同时存在优先取trade_no|HZ0120131127001
operator_id|String|可选|28|商家操作员编号 id，由商家自定义。|YX01

### 同步模式

```php
$res = $instance
->alipay->trade->close
->post([
    'content' => [
        'trade_no'     => '2013112611001004680073956707',
        'out_trade_no' => 'HZ0120131127001',
        'operator_id'  => 'YX01',
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->trade->close
->postAsync([
    'content' => [
        'trade_no'     => '2013112611001004680073956707',
        'out_trade_no' => 'HZ0120131127001',
        'operator_id'  => 'YX01',
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 统一收单交易退款查询接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
trade_no|String|特殊可选|64|支付宝交易号。和商户订单号不能同时为空|2021081722001419121412730660
out_trade_no|String|特殊可选|64|商户订单号。订单支付时传入的商户订单号,和支付宝交易号不能同时为空。 trade_no,out_trade_no如果同时存在优先取trade_no|2014112611001004680073956707
out_request_no|String|必选|64|退款请求号。请求退款接口时，传入的退款请求号，如果在退款请求时未传入，则该值为创建交易时的商户订单号。|HZ01RF001
query_options|String[]|可选|1024|查询选项，商户通过上送该参数来定制同步需要额外返回的信息字段，数组格式。枚举支持：refund_detail_item_list：本次退款使用的资金渠道;gmt_refund_pay：退款执行成功的时间；|refund_detail_item_list


### 同步模式

```php
$res = $instance
->alipay->trade->fastpayRefundQuery
->post([
    'content' => [
        'trade_no'       => '2021081722001419121412730660',
        'out_trade_no'   => '2014112611001004680073956707',
        'out_request_no' => 'HZ01RF001',
        'query_options'  => ['refund_detail_item_list'],
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->trade->fastpayRefundQuery
->postAsync([
    'content' => [
        'trade_no'       => '2021081722001419121412730660',
        'out_trade_no'   => '2014112611001004680073956707',
        'out_request_no' => 'HZ01RF001',
        'query_options'  => ['refund_detail_item_list'],
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 查询对账单下载地址接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---
bill_type|String|必选|10|账单类型，商户通过接口或商户经开放平台授权后其所属服务商通过接口可以获取以下账单类型，支持：trade：商户基于支付宝交易收单的业务账单；signcustomer：基于商户支付宝余额收入及支出等资金变动的账务账单。|trade
bill_date|String|必选|15|账单时间：日账单格式为yyyy-MM-dd，最早可下载2016年1月1日开始的日账单；月账单格式为yyyy-MM，最早可下载2016年1月开始的月账单。|2016-04-05
### 同步模式

```php
$res = $instance
->alipay->data->dataserviceBillDownloadurlQuery
->post([
    'content' => [
        'bill_type' => 'trade',
        'bill_date' => '2016-04-05',
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->data->dataserviceBillDownloadurlQuery
->postAsync([
    'content' => [
        'bill_type' => 'trade',
        'bill_date' => '2016-04-05',
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 统一收单交易支付接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---

### 同步模式

```php
$res = $instance
->alipay->trade->pay
->post([
    'content' => [
        'out_trade_no'     => '20150320010101001',
        'total_amount'     => 88.88,
        'subject'          => 'Iphone6 16G',
        'scene'            => 'bar_code',
        'auth_code'        => '28763443825664394',
        'product_code'     => 'FACE_TO_FACE_PAYMENT',
        'agreement_params' => [
            'agreement_no'    => '20170322450983769228',
            'auth_confirm_no' => '423979',
            'apply_token'     => 'MDEDUCT0068292ca377d1d44b65fa24ec9cd89132f',
        ],
        'auth_no'           => '2016110310002001760201905725',
        'auth_confirm_mode' => 'COMPLETE',
        'seller_id'         => '2088102146225135',
        'buyer_id'          => '2088202954065786',
        'body'              => 'Iphone6 16G',
        'goods_detail'      => [[
            'goods_id'        => 'apple-01',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
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
        'sub_merchant' => [
            'merchant_id'   => '2088000603999128',
            'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
        ],
        'extend_params' => [
            'sys_service_provider_id' => '2088511833207846',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'promo_params' => [
            'actual_order_time' => '2018-09-25 22:47:33',
        ],
        'advance_payment_type' => 'ENJOY_PAY_V2',
        'pay_params'           => [
            'async_type' => 'NORMAL_ASYNC',
        ],
        'is_async_pay'          => false,
        'discountable_amount'   => 80,
        'undiscountable_amount' => 8.88,
        'store_id'              => 'NJ_001',
        'operator_id'           => 'yx_001',
        'terminal_id'           => 'NJ_T_001',
        'request_org_pid'       => '2088201916734621',
        'query_options'         => ['string'],
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->trade->pay
->postAsync([
    'content' => [
        'out_trade_no' => '20150320010101001',
        'total_amount' => 88.88,
        'subject'      => 'Iphone6 16G',
        'product_code' => 'FACE_TO_FACE_PAYMENT',
        'seller_id'    => '2088102146225135',
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
        'undiscountable_amount' => 8.88,
        'discountable_amount'   => 80,
        'store_id'              => 'NJ_001',
        'operator_id'           => 'yx_001',
        'terminal_id'           => 'NJ_T_001',
        'disable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'enable_pay_channels'   => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'     => '20161008001',
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## 统一收单线下交易预创建

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---

### 同步模式

```php
$res = $instance
->alipay->trade->precreate
->post([
    'content' => [
        'out_trade_no' => '20150320010101001',
        'total_amount' => 88.88,
        'subject'      => 'Iphone6 16G',
        'product_code' => 'FACE_TO_FACE_PAYMENT',
        'seller_id'    => '2088102146225135',
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
        'undiscountable_amount' => 8.88,
        'discountable_amount'   => 80,
        'store_id'              => 'NJ_001',
        'operator_id'           => 'yx_001',
        'terminal_id'           => 'NJ_T_001',
        'disable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'enable_pay_channels'   => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'     => '20161008001',
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r(json_decode((string)$res->getBody(), true));
```
### 异步模式

```php
$res = $instance
->alipay->trade->precreate
->postAsync([
    'content' => [
        'out_trade_no'     => '20150320010101001',
        'total_amount'     => 88.88,
        'subject'          => 'Iphone6 16G',
        'scene'            => 'bar_code',
        'auth_code'        => '28763443825664394',
        'product_code'     => 'FACE_TO_FACE_PAYMENT',
        'agreement_params' => [
            'agreement_no'    => '20170322450983769228',
            'auth_confirm_no' => '423979',
            'apply_token'     => 'MDEDUCT0068292ca377d1d44b65fa24ec9cd89132f',
        ],
        'auth_no'           => '2016110310002001760201905725',
        'auth_confirm_mode' => 'COMPLETE',
        'seller_id'         => '2088102146225135',
        'buyer_id'          => '2088202954065786',
        'body'              => 'Iphone6 16G',
        'goods_detail'      => [[
            'goods_id'        => 'apple-01',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
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
        'sub_merchant' => [
            'merchant_id'   => '2088000603999128',
            'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
        ],
        'extend_params' => [
            'sys_service_provider_id' => '2088511833207846',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'promo_params' => [
            'actual_order_time' => '2018-09-25 22:47:33',
        ],
        'advance_payment_type' => 'ENJOY_PAY_V2',
        'pay_params'           => [
            'async_type' => 'NORMAL_ASYNC',
        ],
        'is_async_pay'          => false,
        'discountable_amount'   => 80,
        'undiscountable_amount' => 8.88,
        'store_id'              => 'NJ_001',
        'operator_id'           => 'yx_001',
        'terminal_id'           => 'NJ_T_001',
        'request_org_pid'       => '2088201916734621',
        'query_options'         => ['string'],
    ],
    'query' => [
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return json_decode((string)$res->getBody(), true); })
->wait();
print_r($res);
```

## APP支付2.0接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---

### 同步模式

```php
$res = $instance
->alipay->trade->appPay
->post([
    'pager' => true,
    'content' => [
        'out_trade_no' => '70501111111S001111119',
        'total_amount' => '9.00',
        'subject'      => '大乐透',
        'product_code' => 'QUICK_MSECURITY_PAY',
        'body'         => 'Iphone6 16G',
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:00',
        'timeout_express' => '90m',
        'extend_params'   => [
            'sys_service_provider_id' => '2088511833207846',
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'promo_params'          => '{"storeIdType":"1"}',
        'passback_params'       => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'agreement_sign_params' => [
            'personal_product_code' => 'CYCLE_PAY_AUTH_P',
            'sign_scene'            => 'INDUSTRY|DIGITAL_MEDIA',
            'external_agreement_no' => 'test20190701',
            'external_logon_id'     => '13852852877',
            'access_params'         => [
                'channel' => 'ALIPAYAPP',
            ],
            'channel'      => 'ALIPAYAPP',
            'sub_merchant' => [
                'sub_merchant_id'                  => '2088123412341234',
                'sub_merchant_name'                => '滴滴出行',
                'sub_merchant_service_name'        => '滴滴出行免密支付',
                'sub_merchant_service_description' => '免密付车费，单次最高500',
            ],
            'sub_merchant_id'                  => '2088123412341234',
            'sub_merchant_name'                => '滴滴出行',
            'sub_merchant_service_name'        => '滴滴出行免密支付',
            'sub_merchant_service_description' => '免密付车费，单次最高500',
            'period_rule_params'               => [
                'period_type'    => 'DAY',
                'period'         => 3,
                'execute_time'   => '2019-01-23',
                'single_amount'  => 10.99,
                'total_amount'   => 600,
                'total_payments' => 12,
            ],
            'period_type'     => 'DAY',
            'period'          => 3,
            'execute_time'    => '2019-01-23',
            'single_amount'   => 10.99,
            'total_amount'    => 600,
            'total_payments'  => 12,
            'sign_notify_url' => 'http://www.merchant.com/receiveSignNotify',
        ],
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'specified_channel'    => 'pcredit',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r((string)$res->getBody());
```
### 异步模式

```php
$res = $instance
->alipay->trade->appPay
->postAsync([
    'pager' => true,
    'content' => [
        'out_trade_no' => '70501111111S001111119',
        'total_amount' => '9.00',
        'subject'      => '大乐透',
        'product_code' => 'QUICK_MSECURITY_PAY',
        'body'         => 'Iphone6 16G',
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:00',
        'timeout_express' => '90m',
        'extend_params'   => [
            'sys_service_provider_id' => '2088511833207846',
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'promo_params'          => '{"storeIdType":"1"}',
        'passback_params'       => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'agreement_sign_params' => [
            'personal_product_code' => 'CYCLE_PAY_AUTH_P',
            'sign_scene'            => 'INDUSTRY|DIGITAL_MEDIA',
            'external_agreement_no' => 'test20190701',
            'external_logon_id'     => '13852852877',
            'access_params'         => [
                'channel' => 'ALIPAYAPP',
            ],
            'channel'      => 'ALIPAYAPP',
            'sub_merchant' => [
                'sub_merchant_id'                  => '2088123412341234',
                'sub_merchant_name'                => '滴滴出行',
                'sub_merchant_service_name'        => '滴滴出行免密支付',
                'sub_merchant_service_description' => '免密付车费，单次最高500',
            ],
            'sub_merchant_id'                  => '2088123412341234',
            'sub_merchant_name'                => '滴滴出行',
            'sub_merchant_service_name'        => '滴滴出行免密支付',
            'sub_merchant_service_description' => '免密付车费，单次最高500',
            'period_rule_params'               => [
                'period_type'    => 'DAY',
                'period'         => 3,
                'execute_time'   => '2019-01-23',
                'single_amount'  => 10.99,
                'total_amount'   => 600,
                'total_payments' => 12,
            ],
            'period_type'     => 'DAY',
            'period'          => 3,
            'execute_time'    => '2019-01-23',
            'single_amount'   => 10.99,
            'total_amount'    => 600,
            'total_payments'  => 12,
            'sign_notify_url' => 'http://www.merchant.com/receiveSignNotify',
        ],
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'specified_channel'    => 'pcredit',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return (string)$res->getBody(); })
->wait();
print_r($res);
```

## 统一收单下单并支付页面接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---

### 同步模式

```php
$res = $instance
->alipay->trade->pagePay
->post([
    'pager' => true,
    'content' => [
        'out_trade_no' => '20150320010101001',
        'total_amount' => 88.88,
        'subject'      => 'Iphone6 16G',
        'product_code' => 'FAST_INSTANT_TRADE_PAY',
        'body'         => 'Iphone6 16G',
        'qr_pay_mode'  => '1',
        'qrcode_width' => 100,
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:01',
        'timeout_express' => '90m',
        'royalty_info'    => [
            'royalty_type'         => 'ROYALTY',
            'royalty_detail_infos' => [[
                'serial_no'         => 1,
                'trans_in_type'     => 'userId',
                'batch_no'          => '123',
                'out_relation_id'   => '20131124001',
                'trans_out_type'    => 'userId',
                'trans_out'         => '2088101126765726',
                'trans_in'          => '2088101126708402',
                'amount'            => 0.1,
                'desc'              => '分账测试1',
                'amount_percentage' => '100',
                'sub_merchant'      => [
                    'merchant_id'   => '2088000603999128',
                    'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
                ],
            ],],
            'serial_no'         => 1,
            'trans_in_type'     => 'userId',
            'batch_no'          => '123',
            'out_relation_id'   => '20131124001',
            'trans_out_type'    => 'userId',
            'trans_out'         => '2088101126765726',
            'trans_in'          => '2088101126708402',
            'amount'            => 0.1,
            'desc'              => '分账测试1',
            'amount_percentage' => '100',
        ],
        'sub_merchant' => [
            'merchant_id'   => '2088000603999128',
            'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
        ],
        'settle_info' => [
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
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'business_params'       => '{"data":"123"}',
        'promo_params'          => '{"storeIdType":"1"}',
        'passback_params'       => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'integration_type'      => 'PCWEB',
        'request_from_url'      => 'https://',
        'agreement_sign_params' => [
            'personal_product_code' => 'GENERAL_WITHHOLDING_P',
            'sign_scene'            => 'INDUSTRY|CARRENTAL',
            'external_agreement_no' => 'test',
            'external_logon_id'     => '13852852877',
            'sign_validity_period'  => '2m',
            'third_party_type'      => 'PARTNER',
            'buckle_app_id'         => '1001164',
            'buckle_merchant_id'    => '268820000000414397785',
            'promo_params'          => '{"key","value"}',
        ],
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
        'invoice_info' => [
            'key_info' => [
                'is_support_invoice'    => true,
                'invoice_merchant_name' => 'ABC|003',
                'tax_num'               => '1464888883494',
            ],
            'is_support_invoice'    => true,
            'invoice_merchant_name' => 'ABC|003',
            'tax_num'               => '1464888883494',
            'details'               => '[{"code":"100294400","name":"服饰","num":"2","sumPrice":"200.00","taxRate":"6%"}]',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r((string)$res->getBody());
```

### 异步模式

```php
$res = $instance
->alipay->trade->pagePay
->postAsync([
    'pager' => true,
    'content' => [
        'out_trade_no' => '20150320010101001',
        'total_amount' => 88.88,
        'subject'      => 'Iphone6 16G',
        'product_code' => 'FAST_INSTANT_TRADE_PAY',
        'body'         => 'Iphone6 16G',
        'qr_pay_mode'  => '1',
        'qrcode_width' => 100,
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:01',
        'timeout_express' => '90m',
        'royalty_info'    => [
            'royalty_type'         => 'ROYALTY',
            'royalty_detail_infos' => [[
                'serial_no'         => 1,
                'trans_in_type'     => 'userId',
                'batch_no'          => '123',
                'out_relation_id'   => '20131124001',
                'trans_out_type'    => 'userId',
                'trans_out'         => '2088101126765726',
                'trans_in'          => '2088101126708402',
                'amount'            => 0.1,
                'desc'              => '分账测试1',
                'amount_percentage' => '100',
                'sub_merchant'      => [
                    'merchant_id'   => '2088000603999128',
                    'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
                ],
            ],],
            'serial_no'         => 1,
            'trans_in_type'     => 'userId',
            'batch_no'          => '123',
            'out_relation_id'   => '20131124001',
            'trans_out_type'    => 'userId',
            'trans_out'         => '2088101126765726',
            'trans_in'          => '2088101126708402',
            'amount'            => 0.1,
            'desc'              => '分账测试1',
            'amount_percentage' => '100',
        ],
        'sub_merchant' => [
            'merchant_id'   => '2088000603999128',
            'merchant_type' => 'alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号',
        ],
        'settle_info' => [
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
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'business_params'       => '{"data":"123"}',
        'promo_params'          => '{"storeIdType":"1"}',
        'passback_params'       => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'integration_type'      => 'PCWEB',
        'request_from_url'      => 'https://',
        'agreement_sign_params' => [
            'personal_product_code' => 'GENERAL_WITHHOLDING_P',
            'sign_scene'            => 'INDUSTRY|CARRENTAL',
            'external_agreement_no' => 'test',
            'external_logon_id'     => '13852852877',
            'sign_validity_period'  => '2m',
            'third_party_type'      => 'PARTNER',
            'buckle_app_id'         => '1001164',
            'buckle_merchant_id'    => '268820000000414397785',
            'promo_params'          => '{"key","value"}',
        ],
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
        'invoice_info' => [
            'key_info' => [
                'is_support_invoice'    => true,
                'invoice_merchant_name' => 'ABC|003',
                'tax_num'               => '1464888883494',
            ],
            'is_support_invoice'    => true,
            'invoice_merchant_name' => 'ABC|003',
            'tax_num'               => '1464888883494',
            'details'               => '[{"code":"100294400","name":"服饰","num":"2","sumPrice":"200.00","taxRate":"6%"}]',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return (string)$res->getBody(); })
->wait();
print_r($res);
```

## 手机网站支付接口2.0接口

参数|类型|是否必填|最大长度|描述|示例值
---|---|---|---|---|---

### 同步模式

```php
$res = $instance
->alipay->trade->wapPay
->post([
    'pager' => true,
    'content' => [
        'out_trade_no' => '70501111111S001111119',
        'total_amount' => 9,
        'subject'      => '大乐透',
        'product_code' => 'QUICK_WAP_PAY',
        'auth_token'   => 'appopenBb64d181d0146481ab6a762c00714cC27',
        'body'         => 'Iphone6 16G',
        'quit_url'     => 'http://www.taobao.com/product/113714.html',
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'body'            => '特价手机',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:00',
        'timeout_express' => '90m',
        'extend_params'   => [
            'sys_service_provider_id' => '2088511833207846',
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'business_params'      => '{"data":"123"}',
        'promo_params'         => '{"storeIdType":"1"}',
        'passback_params'      => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'specified_channel'    => 'pcredit',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
]);
print_r((string)$res->getBody());
```

### 同步模式

```php
$res = $instance
->alipay->trade->wapPay
->postAsync([
    'pager' => true,
    'content' => [
        'out_trade_no' => '70501111111S001111119',
        'total_amount' => 9,
        'subject'      => '大乐透',
        'product_code' => 'QUICK_WAP_PAY',
        'auth_token'   => 'appopenBb64d181d0146481ab6a762c00714cC27',
        'body'         => 'Iphone6 16G',
        'quit_url'     => 'http://www.taobao.com/product/113714.html',
        'goods_detail' => [[
            'goods_id'        => 'apple-01',
            'alipay_goods_id' => '20010001',
            'goods_name'      => 'ipad',
            'quantity'        => 1,
            'price'           => 2000,
            'goods_category'  => '34543238',
            'categories_tree' => '124868003|126232002|126252004',
            'body'            => '特价手机',
            'show_url'        => 'http://www.alipay.com/xxx.jpg',
        ],],
        'time_expire'     => '2016-12-31 10:05:00',
        'timeout_express' => '90m',
        'extend_params'   => [
            'sys_service_provider_id' => '2088511833207846',
            'hb_fq_num'               => '3',
            'hb_fq_seller_percent'    => '100',
            'industry_reflux_info'    => '{"scene_code":"metro_tradeorder","channel":"xxxx","scene_data":{"asset_name":"ALIPAY"}}',
            'card_type'               => 'S0JP0000',
            'specified_seller_name'   => 'XXX的跨境小铺',
        ],
        'business_params'      => '{"data":"123"}',
        'promo_params'         => '{"storeIdType":"1"}',
        'passback_params'      => 'merchantBizType%3d3C%26merchantBizNo%3d2016010101111',
        'store_id'             => 'NJ_001',
        'enable_pay_channels'  => 'pcredit,moneyFund,debitCardExpress',
        'disable_pay_channels' => 'pcredit,moneyFund,debitCardExpress',
        'specified_channel'    => 'pcredit',
        'merchant_order_no'    => '20161008001',
        'ext_user_info'        => [
            'name'            => '李明',
            'mobile'          => '16587658765',
            'cert_type'       => 'IDENTITY_CARD',
            'cert_no'         => '362334768769238881',
            'min_age'         => '18',
            'fix_buyer'       => 'F',
            'need_check_info' => 'F',
        ],
    ],
    'query' => [
        'return_url' => 'https://m.alipay.com/Gk8NF23',
        'notify_url' => 'http://api.test.alipay.net/atinterface/receive_notify.htm',
    ],
])
->then(static function($res) { return (string)$res->getBody(); })
->wait();
print_r($res);
```
