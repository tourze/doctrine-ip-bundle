<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(IpTrackListener::class)]
#[RunTestsInSeparateProcesses]
final class IpTrackListenerIntegrationTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试不需要额外的设置
    }

    private function createTestEntity(): TestEntityForIpTracking
    {
        return new TestEntityForIpTracking();
    }

    private function createListener(?LoggerInterface $logger = null, ?PropertyAccessor $propertyAccessor = null): IpTrackListener
    {
        // 设置测试环境
        $logger ??= $this->createMock(LoggerInterface::class);
        $propertyAccessor ??= PropertyAccess::createPropertyAccessor();

        // 创建监听器实例用于集成测试
        $reflection = new \ReflectionClass(IpTrackListener::class);
        $listener = $reflection->newInstance($logger, $propertyAccessor);

        // 设置客户端IP
        $listener->setClientIp('192.168.10.1');

        return $listener;
    }

    private function createObjectManager(object $entity): ObjectManager
    {
        // 设置模拟的ObjectManager
        $reflection = new \ReflectionClass($entity::class);

        // 使用具体类进行Mock，原因：1）ClassMetadata是Doctrine的核心类，接口不好模拟，2）需要特定的反射功能，3）这是集成测试中的必要组件
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getReflectionClass')
            ->willReturn($reflection)
        ;

        // 使用接口进行Mock，原因：1）ObjectManager是Doctrine的核心接口，2）清晰定义了需要的方法，3）提高测试的可维护性
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')
            ->with($entity::class)
            ->willReturn($classMetadata)
        ;

        return $objectManager;
    }

    public function testPrePersistEntity(): void
    {
        $entity = $this->createTestEntity();
        $listener = $this->createListener();
        $objectManager = $this->createObjectManager($entity);

        // 测试前记录原始值
        $originalCreateIp = $entity->getCreatedFromIp();

        // PropertyAccessor 将直接设置值

        // 执行测试
        $listener->prePersistEntity($objectManager, $entity);

        // 验证结果 - 手动检查值是否已被设置
        $this->assertNotEquals($originalCreateIp, $entity->getCreatedFromIp());
        $this->assertEquals('192.168.10.1', $entity->getCreatedFromIp());
    }

    public function testPrePersistEntityWithProtectedPropertyFromParent(): void
    {
        $entity = new TestChildEntityForIpTracking();
        $listener = $this->createListener();
        $objectManager = $this->createObjectManager($entity);

        $this->assertNull($entity->getCreatedFromIp());

        $listener->prePersistEntity($objectManager, $entity);

        $this->assertEquals('192.168.10.1', $entity->getCreatedFromIp());
    }

    public function testPreUpdateEntity(): void
    {
        $entity = $this->createTestEntity();
        $listener = $this->createListener();
        $objectManager = $this->createObjectManager($entity);

        // 测试前记录原始值
        $originalUpdateIp = $entity->getUpdatedFromIp();

        // PropertyAccessor 将直接设置值

        // 使用具体类进行Mock，原因：1）PreUpdateEventArgs是Doctrine事件系统的核心类，2）需要特定的事件数据，3）这是与Doctrine ORM的集成测试
        $args = $this->createMock(PreUpdateEventArgs::class);

        // 执行测试
        $listener->preUpdateEntity($objectManager, $entity, $args);

        // 验证结果 - 手动检查值是否已被设置
        $this->assertNotEquals($originalUpdateIp, $entity->getUpdatedFromIp());
        $this->assertEquals('192.168.10.1', $entity->getUpdatedFromIp());
    }

    public function testPreUpdateEntityWithProtectedPropertyFromParent(): void
    {
        $entity = new TestChildEntityForIpTracking();
        $listener = $this->createListener();
        $objectManager = $this->createObjectManager($entity);
        $args = $this->createMock(PreUpdateEventArgs::class);

        $this->assertNull($entity->getUpdatedFromIp());

        $listener->preUpdateEntity($objectManager, $entity, $args);

        $this->assertEquals('192.168.10.1', $entity->getUpdatedFromIp());
    }

    public function testPrePersistWithNullIp(): void
    {
        $entity = $this->createTestEntity();
        $listener = $this->createListener();
        $objectManager = $this->createObjectManager($entity);

        // 记录原始值
        $originalCreateIp = $entity->getCreatedFromIp();

        // 重置客户端IP为null
        $listener->setClientIp(null);

        // PropertyAccessor 不应该设置任何值，因为IP为null

        // 执行测试
        $listener->prePersistEntity($objectManager, $entity);

        // 验证结果 - 值应该保持不变
        $this->assertEquals($originalCreateIp, $entity->getCreatedFromIp());
        $this->assertNull($entity->getCreatedFromIp());
    }

    public function testOnKernelRequest(): void
    {
        $listener = $this->createListener();

        $request = $this->createMock(Request::class);
        $request->method('getClientIp')
            ->willReturn('10.0.0.1')
        ;

        // 使用具体类进行Mock，原因：1）RequestEvent是Symfony核心事件类，需要特定的Request处理逻辑，2）没有对应的接口可以使用，3）这是测试HTTP请求处理的必要组件
        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->method('getRequest')
            ->willReturn($request)
        ;

        $listener->onKernelRequest($requestEvent);

        $this->assertEquals('10.0.0.1', $listener->getClientIp());
    }

    public function testReset(): void
    {
        $listener = $this->createListener();
        $listener->setClientIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $listener->getClientIp());

        $listener->reset();

        $this->assertNull($listener->getClientIp());
    }
}
