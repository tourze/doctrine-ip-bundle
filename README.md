# Doctrine IP Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-ip-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/doctrine-ip-bundle/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/doctrine-ip-bundle/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/doctrine-ip-bundle?style=flat-square)](https://codecov.io/gh/tourze/doctrine-ip-bundle)


A Symfony bundle that automatically tracks and records IP addresses for entity creation and
updates using PHP 8.1 attributes. This bundle provides seamless integration with Doctrine ORM
to capture client IP addresses during entity lifecycle events.

## Features

- 🔍 **Automatic IP Tracking**: Automatically captures client IP addresses during entity creation and updates
- 🏷️ **PHP 8.1 Attributes**: Uses modern PHP attributes for simple, declarative configuration
- 🔐 **Private Property Support**: Works with private properties through Symfony's PropertyAccessor
- 🧵 **Thread-Safe**: Implementation with ResetInterface for proper request isolation
- 🔄 **Seamless Integration**: Integrates with Symfony's request cycle and Doctrine lifecycle events
- 📦 **Zero Configuration**: Works out of the box with no additional setup required
- 🎯 **Flexible**: Supports both individual attributes and convenient traits

## Installation

### Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 2.20/3.0 or higher
- Doctrine Bundle 2.13 or higher

### Install via Composer

```bash
composer require tourze/doctrine-ip-bundle
```

The bundle will be automatically registered thanks to Symfony Flex.

## Quick Start

### Method 1: Using Individual Attributes

Add the appropriate attributes to your entity properties:

```php
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    // This property will store the IP address when the entity is created
    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $createIp = null;

    // This property will store the IP address when the entity is updated
    #[UpdateIpColumn]
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $updateIp = null;

    // Getters and setters
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

## Method 2: Using Convenient Traits

For easier implementation, use the provided traits:

```php
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    use IpTraceableAware;
    
    // Your other properties...
}
```

Or use the creation-only trait:

```php
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class YourEntity
{
    use CreatedFromIpAware;
    
    // Your other properties...
}
```

## Available Attributes and Traits

### Attributes

- **`#[CreateIpColumn]`**: Marks a property to be automatically populated with the client
  IP address when the entity is first persisted
- **`#[UpdateIpColumn]`**: Marks a property to be automatically populated with the client
  IP address when the entity is updated

### Traits

- **`IpTraceableAware`**: Provides both `$createdFromIp` and `$updatedFromIp` properties with getters and setters
- **`CreatedFromIpAware`**: Provides only `$createdFromIp` property with getter and setter

Both traits include the proper Doctrine ORM mapping annotations.

## How It Works

The bundle works by listening to Doctrine's lifecycle events:

1. It registers event listeners for Doctrine's `prePersist` and `preUpdate` events
2. It captures the client IP from the request through Symfony's `kernel.request` event
3. When an entity is created or updated, it checks for properties with the appropriate attributes
4. If found, it automatically sets the client IP to those properties

## Configuration

The bundle is auto-configured and requires no additional configuration. It automatically:

- Registers the IP tracking listener with Doctrine
- Configures the PropertyAccessor for private property access
- Sets up the request listener to capture client IP addresses

## Advanced Usage

### Custom IP Detection

The bundle automatically detects client IP addresses using Symfony's `Request::getClientIp()` method, which handles:

- Standard `REMOTE_ADDR` header
- Proxy headers like `X-Forwarded-For`
- Load balancer headers like `X-Real-IP`

### Thread Safety

The bundle implements Symfony's `ResetInterface` to ensure proper request isolation
in long-running processes like ReactPHP or Swoole.

### Existing Values

The bundle respects existing values - if a property already has a value, it will not be overwritten.

## Dependencies

This bundle automatically requires and configures:

- `tourze/doctrine-entity-checker-bundle` - For entity validation and checking capabilities

## License

This bundle is licensed under the MIT License.
