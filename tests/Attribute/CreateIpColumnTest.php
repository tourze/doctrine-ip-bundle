<?php

namespace Tourze\DoctrineIpBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;

class CreateIpColumnTest extends TestCase
{
    public function testAttributeCreation(): void
    {
        // 测试能否成功创建属性实例
        $attribute = new CreateIpColumn();
        $this->assertInstanceOf(CreateIpColumn::class, $attribute);
    }

    public function testAttributeTargetProperty(): void
    {
        // 测试属性是否正确标记为只能用于属性
        $reflection = new \ReflectionClass(CreateIpColumn::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes, 'CreateIpColumn类应该有一个Attribute属性');

        $attribute = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_PROPERTY, $attribute->flags);
    }
}
