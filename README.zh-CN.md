# Doctrine IP Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)

一个使用 PHP 8.1 属性自动跟踪和记录实体创建和更新时 IP 地址的 Symfony 包。

## 功能特性

- 自动跟踪实体创建时的 IP 地址
- 自动跟踪实体更新时的 IP 地址
- 使用 PHP 8.1 属性进行简单配置
- 通过 PropertyAccessor 支持私有属性
- 使用 ResetInterface 实现线程安全
- 与 Symfony 的请求周期集成以捕获客户端 IP
- 零配置 - 开箱即用

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 2.20/3.0 或更高版本
- Doctrine Bundle 2.13 或更高版本

## 安装

```bash
composer require tourze/doctrine-ip-bundle
```

由于 Symfony Flex 的支持，包会被自动注册。

## 使用方法

在实体属性上添加相应的属性：

```php
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

class YourEntity
{
    // 此属性将存储实体创建时的 IP 地址
    #[CreateIpColumn]
    private ?string $createIp = null;

    // 此属性将存储实体更新时的 IP 地址
    #[UpdateIpColumn]
    private ?string $updateIp = null;

    // Getter 和 Setter 方法
    public function getCreateIp(): ?string
    {
        return $this->createIp;
    }

    public function getUpdateIp(): ?string
    {
        return $this->updateIp;
    }
}
```

## 工作原理

该包通过监听 Doctrine 的生命周期事件工作：

1. 它为 Doctrine 的 `prePersist` 和 `preUpdate` 事件注册事件监听器
2. 通过 Symfony 的 `kernel.request` 事件捕获客户端 IP
3. 当实体被创建或更新时，它会检查带有相应属性的属性
4. 如果找到，它会自动将客户端 IP 设置到这些属性中

## 配置

该包是自动配置的，无需额外配置。

## 依赖

此包自动需要并配置：

- `tourze/doctrine-entity-checker-bundle`

## 许可证

此包基于 MIT 许可证。
