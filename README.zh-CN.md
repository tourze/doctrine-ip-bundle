# Doctrine IP Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-ip-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/doctrine-ip-bundle/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/doctrine-ip-bundle/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/doctrine-ip-bundle?style=flat-square)](https://codecov.io/gh/tourze/doctrine-ip-bundle)


一个使用 PHP 8.1 属性自动跟踪和记录实体创建和更新时 IP 地址的 Symfony 包。该包提供与 Doctrine ORM 的无缝集成，在实体生命周期事件中捕获客户端 IP 地址。

## 功能特性

- 🔍 **自动 IP 跟踪**：在实体创建和更新时自动捕获客户端 IP 地址
- 🏷️ **PHP 8.1 属性**：使用现代 PHP 属性进行简单的声明式配置
- 🔐 **私有属性支持**：通过 Symfony 的 PropertyAccessor 支持私有属性
- 🧵 **线程安全**：使用 ResetInterface 实现适当的请求隔离
- 🔄 **无缝集成**：与 Symfony 的请求周期和 Doctrine 生命周期事件集成
- 📦 **零配置**：开箱即用，无需额外设置
- 🎯 **灵活性**：支持单独属性和便利特性

## 安装

### 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 2.20/3.0 或更高版本
- Doctrine Bundle 2.13 或更高版本

### 通过 Composer 安装

```bash
composer require tourze/doctrine-ip-bundle
```

由于 Symfony Flex 的支持，包会被自动注册。

## 快速开始

### 方法 1：使用单独属性

在实体属性上添加相应的属性：

```php
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    // 此属性将存储实体创建时的 IP 地址
    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $createIp = null;

    // 此属性将存储实体更新时的 IP 地址
    #[UpdateIpColumn]
    #[ORM\Column(length: 45, nullable: true)]
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

## 方法 2：使用便利特性

为了更容易的实现，可以使用提供的特性：

```php
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    use IpTraceableAware;
    
    // 你的其他属性...
}
```

或使用仅创建时的特性：

```php
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    use CreatedFromIpAware;
    
    // 你的其他属性...
}
```

## 可用属性和特性

### 属性

- **`#[CreateIpColumn]`**：标记属性在实体首次持久化时自动填充客户端 IP 地址
- **`#[UpdateIpColumn]`**：标记属性在实体更新时自动填充客户端 IP 地址

### 特性

- **`IpTraceableAware`**：提供 `$createdFromIp` 和 `$updatedFromIp` 属性及其 getter 和 setter 方法
- **`CreatedFromIpAware`**：仅提供 `$createdFromIp` 属性及其 getter 和 setter 方法

两个特性都包含适当的 Doctrine ORM 映射注解。

## 工作原理

该包通过监听 Doctrine 的生命周期事件工作：

1. 它为 Doctrine 的 `prePersist` 和 `preUpdate` 事件注册事件监听器
2. 通过 Symfony 的 `kernel.request` 事件捕获客户端 IP
3. 当实体被创建或更新时，它会检查带有相应属性的属性
4. 如果找到，它会自动将客户端 IP 设置到这些属性中

## 配置

该包是自动配置的，无需额外配置。它会自动：

- 向 Doctrine 注册 IP 跟踪监听器
- 配置 PropertyAccessor 以支持私有属性访问
- 设置请求监听器以捕获客户端 IP 地址

## 高级用法

### 自定义 IP 检测

该包使用 Symfony 的 `Request::getClientIp()` 方法自动检测客户端 IP 地址，它处理：

- 标准 `REMOTE_ADDR` 头
- 代理头如 `X-Forwarded-For`
- 负载均衡器头如 `X-Real-IP`

### 线程安全

该包实现了 Symfony 的 `ResetInterface` 以确保在长时间运行的进程（如 ReactPHP 或 Swoole）中的适当请求隔离。

### 现有值

该包尊重现有值 - 如果属性已经有值，它不会被覆盖。

## 依赖

此包自动需要并配置：

- `tourze/doctrine-entity-checker-bundle` - 用于实体验证和检查功能

## 许可证

此包基于 MIT 许可证。
