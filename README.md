# Doctrine IP Bundle

A Symfony bundle that automatically tracks IP addresses for entity creation and updates.

一个用于自动追踪实体创建和更新时 IP 地址的 Symfony 包。

## Features | 功能

- Automatically tracks IP addresses when entities are created | 自动追踪实体创建时的 IP 地址
- Automatically tracks IP addresses when entities are updated | 自动追踪实体更新时的 IP 地址
- Uses PHP 8.1 attributes for configuration | 使用 PHP 8.1 属性进行配置
- Supports private properties | 支持私有属性
- Thread-safe implementation | 线程安全实现

## Requirements | 要求

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher

## Installation | 安装

```bash
composer require tourze/doctrine-ip-bundle
```

## Usage | 使用方法

Add the following attributes to your entity properties:

在你的实体属性上添加以下属性：

```php
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

class YourEntity
{
    #[CreateIpColumn]
    private ?string $createIp = null;

    #[UpdateIpColumn]
    private ?string $updateIp = null;
}
```

## Configuration | 配置

The bundle is auto-configured. No additional configuration is needed.

该包是自动配置的，无需额外配置。

## How it works | 工作原理

1. The bundle listens to Doctrine's `prePersist` and `preUpdate` events
2. It also listens to Symfony's `kernel.request` event to get the client IP
3. When an entity is created or updated, it automatically sets the IP address to the configured properties

1. 该包监听 Doctrine 的 `prePersist` 和 `preUpdate` 事件
2. 同时监听 Symfony 的 `kernel.request` 事件以获取客户端 IP
3. 当实体被创建或更新时，自动将 IP 地址设置到配置的属性中

## License | 许可证

This bundle is licensed under the MIT License.

该包基于 MIT 许可证。
