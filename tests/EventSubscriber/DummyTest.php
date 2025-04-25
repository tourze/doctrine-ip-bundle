<?php

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;

/**
 * 简单的测试类，以确保PHPUnit可以正常运行
 */
class DummyTest extends TestCase
{
    public function testBundleExists(): void
    {
        $bundle = new DoctrineIpBundle();
        $this->assertInstanceOf(DoctrineIpBundle::class, $bundle);
    }

    public function testAttributesExist(): void
    {
        $createIpAttr = new CreateIpColumn();
        $updateIpAttr = new UpdateIpColumn();

        $this->assertInstanceOf(CreateIpColumn::class, $createIpAttr);
        $this->assertInstanceOf(UpdateIpColumn::class, $updateIpAttr);
    }
}
