<?php

namespace Tourze\DoctrineIpBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineEntityCheckerBundle\DoctrineEntityCheckerBundle;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;

class DoctrineIpBundleTest extends TestCase
{
    public function testBundleImplementsDependencyInterface(): void
    {
        // 测试包是否实现了BundleDependencyInterface接口
        $bundle = new DoctrineIpBundle();
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testBundleDependencies(): void
    {
        // 测试依赖包设置是否正确
        $dependencies = DoctrineIpBundle::getBundleDependencies();

        $this->assertArrayHasKey(DoctrineEntityCheckerBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineEntityCheckerBundle::class]);
    }
}
