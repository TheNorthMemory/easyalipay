# 变更历史

## [0.3.4](../../compare/0.3.3...0.3.4) - 2023-01-08

- 使用`chr`代替`sprintf`，性能提高了一点点；
- 支持以`json`作为关键字，来描述`biz_content`数据结构；

## [0.3.3](../../compare/0.3.2...0.3.3) - 2021-11-06

- 优化`Rsa::parse`代码逻辑，去除`is_resource`/`is_object`检测;
- 调整`Rsa::from[Pkcs8|Pkcs1|Spki]`加载语法糖实现，以`Rsa::from`为统一入口；

## [0.3.2](../../compare/v0.3.1...0.3.2) - 2021-11-03

- 新增`phpstan/phpstan:^1.0`支持；
- 优化代码，消除函数内部不安全的`Unsafe call to private|protected method|property ... through static::`调用隐患；
- 优化`Makefile`生成大数逻辑，贴近真实序列号情况；

## [0.3.1](../../compare/v0.3.0...v0.3.1) - 2021-10-17

- 调整`composer.json`，去除`version`字典；

## [0.3.0](../../compare/v0.2.0...v0.3.0) - 2021-10-17

- 新增`Guzzle6`+`PHP7.1`及`Guzzle7`+`PHP8.1`支持；
- 调整`\EasyAlipay\Crypto\Rsa::from`方法，增加第二入参`$type(private|public)`，显示声明第一入参类型；
- 调整`\EasyAlipay\Crypto\Rsa::fromPkcs1`方法的第二入参为`$type(private|public)`，兼容布尔量声明方式；

## [0.2.0](../../compare/v0.1.0...v0.2.0) - 2021-08-20

- 新增`\EasyAlipay\Helpers`类，以支持`公钥证书模式`使用；
- 新增`\EasyAlipay\Crypto\Rsa::pkcs1ToSpki`转换函数，以支持加载`PKCS#1`格式的`RSA公钥`；
- 新增`\EasyAlipay\ClientDecoratorInterface::getClient`接口函数，支持获取客户端实例；
- 新增测试用例覆盖`PHP7.2/7.3/7.4/8.0+Linux/macOS/Windows`运行时；
- 新增`Makefile`模拟工具，`RSA私钥`、`RSA公钥`、`X509证书`相关测试配套组件，由模拟工具生产；

## 0.1.0 - 2021-08-14

第一版，生产可用。
