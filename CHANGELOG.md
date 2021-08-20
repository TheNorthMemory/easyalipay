# 变更历史

## 0.2.0 - 2021-08-20

[变更细节](../../compare/v0.1.0...v0.2.0)

- 新增`\EasyAlipay\Helpers`类，以支持`公钥证书模式`使用；
- 新增`\EasyAlipay\Crypto\Rsa::pkcs1ToSpki`转换函数，以支持加载`PKCS#1`格式的`RSA公钥`；
- 新增`\EasyAlipay\ClientDecoratorInterface::getClient`接口函数，支持获取客户端实例；
- 新增`\EasyAlipay\ClientDecoratorInterface::getClient`接口函数，支持获取客户端实例；
- 新增测试用例覆盖`PHP7.2/7.3/7.4/8.0+Linux/macOS/Windows`运行时；
- 新增`Makefile`模拟工具，`RSA私钥`、`RSA公钥`、`X509证书`相关测试配套组件，由模拟工具生产；

## 0.1.0 - 2021-08-14

第一版，生产可用。
