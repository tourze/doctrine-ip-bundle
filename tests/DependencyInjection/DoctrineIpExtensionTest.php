<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineIpBundle\DependencyInjection\DoctrineIpExtension;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineIpExtension::class)]
final class DoctrineIpExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private DoctrineIpExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new DoctrineIpExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testExtensionLoadsServicesCorrectly(): void
    {
        $this->extension->load([], $this->container);

        // 检查IpTrackListener服务是否可用
        $this->assertTrue(
            $this->container->has(IpTrackListener::class),
            'IpTrackListener服务应该在容器中注册'
        );
    }

    public function testPropertyAccessorServiceRegistered(): void
    {
        $this->extension->load([], $this->container);

        // 检查PropertyAccessor服务定义
        $this->assertTrue(
            $this->container->has('doctrine-ip.property-accessor'),
            '容器中应该有PropertyAccessor服务'
        );

        $propertyAccessor = $this->container->get('doctrine-ip.property-accessor');
        $this->assertInstanceOf(PropertyAccessor::class, $propertyAccessor);
    }
}
