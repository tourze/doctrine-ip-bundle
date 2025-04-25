<?php

namespace Tourze\DoctrineIpBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineIpBundle\DependencyInjection\DoctrineIpExtension;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;

class DoctrineIpExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        // 测试扩展类加载配置
        $extension = new DoctrineIpExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        // 检查服务定义是否正确加载
        $this->assertTrue($container->hasDefinition(IpTrackListener::class) ||
            $container->hasAlias(IpTrackListener::class),
            '容器中应该有IpTrackListener服务的定义');
    }
}
