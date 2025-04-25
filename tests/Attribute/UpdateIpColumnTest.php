<?php

namespace Tourze\DoctrineIpBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

class UpdateIpColumnTest extends TestCase
{
    public function testAttributeCreation(): void
    {
        // 测试能否成功创建属性实例
        $attribute = new UpdateIpColumn();
        $this->assertInstanceOf(UpdateIpColumn::class, $attribute);
    }

    public function testAttributeTargetProperty(): void
    {
        // 测试属性是否正确标记为只能用于属性
        $reflection = new \ReflectionClass(UpdateIpColumn::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes, 'UpdateIpColumn类应该有一个Attribute属性');

        $attribute = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_PROPERTY, $attribute->flags);
    }
}
