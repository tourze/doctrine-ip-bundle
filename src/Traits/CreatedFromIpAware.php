<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;

/**
 * 自动记录实体创建时IP信息的特征.
 *
 * 使用此特征可以为实体添加创建时IP地址跟踪功能。
 * 适用于只需要记录创建来源而不需要跟踪更新来源的业务场景。
 *
 * 使用示例：
 * ```php
 * class User {
 *     use CreatedFromIpAware; // 使用CreatedFromIpAware特性
 *
 *     // 其他属性和方法...
 * }
 * ```
 *
 * 功能特性：
 * - 自动记录创建时IP地址
 * - 支持IPv4和IPv6格式
 * - 轻量级，只跟踪创建操作
 * - 零配置，即插即用
 */
trait CreatedFromIpAware
{
    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    /**
     * 获取创建时的IP地址.
     *
     * @return string|null 创建时的IP地址，如果未设置则返回null
     */
    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    /**
     * 设置创建时的IP地址.
     *
     * @param string|null $createdFromIp 创建时的IP地址
     */
    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }
}
