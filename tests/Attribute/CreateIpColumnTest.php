<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;

/**
 * @internal
 */
#[CoversClass(CreateIpColumn::class)]
final class CreateIpColumnTest extends TestCase
{
    public function testAttributeCreation(): void
    {
        $attribute = new CreateIpColumn();
        $this->assertInstanceOf(CreateIpColumn::class, $attribute);
    }

    public function testAttributeTargetProperty(): void
    {
        $reflection = new \ReflectionClass(CreateIpColumn::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes, 'CreateIpColumn类应该有一个Attribute属性');

        $attribute = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_PROPERTY, $attribute->flags);
    }
}
