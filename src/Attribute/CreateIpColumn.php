<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Attribute;

/**
 * 记录实体创建时IP地址的属性.
 *
 * 将此属性添加到实体的属性上，该属性将在实体创建时自动填充客户端IP地址。
 * 支持IPv4和IPv6地址格式。
 *
 * 使用示例：
 * ```php
 * #[CreateIpColumn]
 * #[ORM\Column(length: 45, nullable: true)]
 * private ?string $createdFromIp = null;
 * ```
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
class CreateIpColumn
{
}
