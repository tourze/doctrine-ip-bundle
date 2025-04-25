<?php

namespace Tourze\DoctrineIpBundle\Tests\Entity;

use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

class TestEntity
{
    #[CreateIpColumn]
    private ?string $createIp = null;

    #[UpdateIpColumn]
    private ?string $updateIp = null;

    private ?string $normalField = null;

    // 带值的字段
    #[CreateIpColumn]
    private string $existingCreateIp = '127.0.0.1';

    #[UpdateIpColumn]
    private string $existingUpdateIp = '127.0.0.1';

    public function getCreateIp(): ?string
    {
        return $this->createIp;
    }

    public function getUpdateIp(): ?string
    {
        return $this->updateIp;
    }

    public function setCreateIp(?string $ip): self
    {
        $this->createIp = $ip;
        return $this;
    }

    public function setUpdateIp(?string $ip): self
    {
        $this->updateIp = $ip;
        return $this;
    }

    public function getNormalField(): ?string
    {
        return $this->normalField;
    }

    public function setNormalField(?string $value): self
    {
        $this->normalField = $value;
        return $this;
    }

    public function getExistingCreateIp(): string
    {
        return $this->existingCreateIp;
    }

    public function getExistingUpdateIp(): string
    {
        return $this->existingUpdateIp;
    }
}
