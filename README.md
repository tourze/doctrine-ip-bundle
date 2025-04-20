# Doctrine IP Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-ip-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-ip-bundle)

A Symfony bundle that automatically tracks and records IP addresses for entity creation and updates using PHP 8.1 attributes.

## Features

- Automatically tracks IP addresses when entities are created
- Automatically tracks IP addresses when entities are updated
- Uses PHP 8.1 attributes for simple configuration
- Supports private properties through PropertyAccessor
- Thread-safe implementation with ResetInterface
- Integrates with Symfony's request cycle to capture client IPs
- Zero configuration required - works out of the box

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 2.20/3.0 or higher
- Doctrine Bundle 2.13 or higher

## Installation

```bash
composer require tourze/doctrine-ip-bundle
```

The bundle will be automatically registered thanks to Symfony Flex.

## Usage

Add the appropriate attributes to your entity properties:

```php
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

class YourEntity
{
    // This property will store the IP address when the entity is created
    #[CreateIpColumn]
    private ?string $createIp = null;

    // This property will store the IP address when the entity is updated
    #[UpdateIpColumn]
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

## How It Works

The bundle works by listening to Doctrine's lifecycle events:

1. It registers event listeners for Doctrine's `prePersist` and `preUpdate` events
2. It captures the client IP from the request through Symfony's `kernel.request` event
3. When an entity is created or updated, it checks for properties with the appropriate attributes
4. If found, it automatically sets the client IP to those properties

## Configuration

The bundle is auto-configured. No additional configuration is needed.

## Dependencies

This bundle automatically requires and configures:

- `tourze/doctrine-entity-checker-bundle`

## License

This bundle is licensed under the MIT License.
