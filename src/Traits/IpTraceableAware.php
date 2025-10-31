<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Traits;

use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

/**
 * 自动记录实体创建和更新时IP信息的特征.
 *
 * 使用此特征可以快速为实体添加IP跟踪功能，包括创建时和更新时的IP地址。
 * 适用于需要审计实体操作来源的业务场景。
 *
 * 使用示例：
 * ```php
 * class User {
 *     use IpTraceableAware;
 *
 *     // 其他属性和方法...
 * }
 * ```
 *
 * 功能特性：
 * - 自动记录创建时IP地址
 * - 自动记录更新时IP地址
 * - 支持IPv4和IPv6格式
 * - 零配置，即插即用
 */
trait IpTraceableAware
{
    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

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

    /**
     * 获取更新时的IP地址.
     *
     * @return string|null 最后更新时的IP地址，如果未设置则返回null
     */
    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    /**
     * 设置更新时的IP地址.
     *
     * @param string|null $updatedFromIp 更新时的IP地址
     */
    public function setUpdatedFromIp(?string $updatedFromIp): void
    {
        $this->updatedFromIp = $updatedFromIp;
    }
}
