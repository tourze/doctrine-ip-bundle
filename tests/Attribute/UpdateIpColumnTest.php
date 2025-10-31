<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

/**
 * @internal
 */
#[CoversClass(UpdateIpColumn::class)]
final class UpdateIpColumnTest extends TestCase
{
    public function testAttributeCreation(): void
    {
        $attribute = new UpdateIpColumn();
        $this->assertInstanceOf(UpdateIpColumn::class, $attribute);
    }

    public function testAttributeTargetProperty(): void
    {
        $reflection = new \ReflectionClass(UpdateIpColumn::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes, 'UpdateIpColumn类应该有一个Attribute属性');

        $attribute = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_PROPERTY, $attribute->flags);
    }
}
