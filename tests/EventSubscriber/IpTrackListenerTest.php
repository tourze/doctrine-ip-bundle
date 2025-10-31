<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * @internal
 */
#[CoversClass(IpTrackListener::class)]
#[RunTestsInSeparateProcesses]
final class IpTrackListenerTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // 单元测试不需要额外的设置
    }

    private function createListener(?LoggerInterface $logger = null, ?PropertyAccessor $propertyAccessor = null): IpTrackListener
    {
        // 设置测试环境
        // 使用接口进行Mock，原因：1）减少测试对具体实现的依赖，2）让测试更专注于行为验证，3）提高测试的可维护性
        $logger ??= $this->createMock(LoggerInterface::class);
        // 使用具体类进行Mock，原因：1）PropertyAccessor是Symfony的成熟组件，接口复杂，2）测试需要特定的属性访问功能，3）这是对外部工具的集成测试
        $propertyAccessor ??= $this->createMock(PropertyAccessor::class);

        // 创建监听器实例用于单元测试
        $reflection = new \ReflectionClass(IpTrackListener::class);

        return $reflection->newInstance($logger, $propertyAccessor);
    }

    public function testImplementsInterfaces(): void
    {
        // 测试类实现了所需接口
        $listener = $this->createListener();
        $reflection = new \ReflectionClass($listener);
        $this->assertTrue($reflection->implementsInterface(ResetInterface::class));
        $this->assertTrue($reflection->implementsInterface(EntityCheckerInterface::class));
    }

    public function testGetSetClientIp(): void
    {
        // 测试设置和获取客户端IP的功能
        $listener = $this->createListener();
        $this->assertNull($listener->getClientIp());

        $testIp = '192.168.1.1';
        $listener->setClientIp($testIp);

        $this->assertEquals($testIp, $listener->getClientIp());
    }

    public function testReset(): void
    {
        // 测试reset()方法是否正确清除IP
        $listener = $this->createListener();
        $listener->setClientIp('192.168.1.1');
        $listener->reset();

        $this->assertNull($listener->getClientIp());
    }

    public function testOnKernelRequest(): void
    {
        // 测试请求事件处理
        // 使用具体类进行Mock，原因：1）Request是Symfony框架的核心类，接口不好模拟，2）测试需要特定的getClientIp方法，3）这是对外部依赖的集成测试
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')
            ->willReturn('192.168.1.1')
        ;

        // 使用具体类进行Mock，原因：1）RequestEvent需要与内核的交互，2）测试需要特定的事件处理逻辑，3）这是与Symfony系统的集成测试
        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->method('getRequest')
            ->willReturn($request)
        ;

        $listener = $this->createListener();
        $listener->onKernelRequest($requestEvent);

        $this->assertEquals('192.168.1.1', $listener->getClientIp());
    }

    public function testPrePersistWithNoClientIp(): void
    {
        // 测试没有客户端IP时的行为 - 直接使用实体方法而不是event arg
        $listener = $this->createListener();
        $listener->setClientIp(null);

        // 使用接口进行Mock，原因：1）ObjectManager是Doctrine的核心接口，2）清晰定义了需要的方法，3）提高测试的可维护性
        $objectManager = $this->createMock(ObjectManager::class);
        $entity = new class {
            #[CreateIpColumn]
            private ?string $createIp = null;

            public function getCreateIp(): ?string
            {
                return $this->createIp;
            }

            public function setCreateIp(?string $createIp): void
            {
                $this->createIp = $createIp;
            }
        };

        // 直接调用实体处理方法，不使用final类的PrePersistEventArgs
        $listener->prePersistEntity($objectManager, $entity);

        $this->assertNull($entity->getCreateIp());
    }

    public function testDoctrineAttributesExist(): void
    {
        // 测试Doctrine事件监听器属性是否存在
        $reflection = new \ReflectionClass(IpTrackListener::class);

        $doctrineAttributes = $reflection->getAttributes(AsDoctrineListener::class);
        $this->assertGreaterThanOrEqual(2, count($doctrineAttributes), 'IpTrackListener应该有至少2个AsDoctrineListener属性');

        $eventTypes = [];
        foreach ($doctrineAttributes as $attr) {
            $instance = $attr->newInstance();
            $eventTypes[] = $instance->event;
        }

        $this->assertContains(Events::prePersist, $eventTypes, '应该监听prePersist事件');
        $this->assertContains(Events::preUpdate, $eventTypes, '应该监听preUpdate事件');
    }

    public function testSymfonyAttributesExist(): void
    {
        // 测试Symfony事件监听器属性是否存在
        $reflection = new \ReflectionMethod(IpTrackListener::class, 'onKernelRequest');

        $eventAttributes = $reflection->getAttributes(AsEventListener::class);
        $this->assertCount(1, $eventAttributes, 'onKernelRequest方法应该有一个AsEventListener属性');

        $instance = $eventAttributes[0]->newInstance();
        $this->assertEquals(KernelEvents::REQUEST, $instance->event, '应该监听KernelEvents::REQUEST事件');
        $this->assertEquals(4096, $instance->priority, '优先级应该是4096');
    }

    public function testPrePersistEntity(): void
    {
        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $listener = $this->createListener(propertyAccessor: $propertyAccessor);
        $listener->setClientIp('127.0.0.1');

        $objectManager = $this->createMock(ObjectManager::class);
        $entity = new class {
            #[CreateIpColumn]
            private ?string $createIp = null;

            public function getCreateIp(): ?string
            {
                return $this->createIp;
            }

            public function setCreateIp(?string $createIp): void
            {
                $this->createIp = $createIp;
            }
        };

        $propertyAccessor->expects($this->once())
            ->method('isWritable')
            ->with($entity, 'createIp')
            ->willReturn(true)
        ;

        $propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($entity, 'createIp', '127.0.0.1')
        ;

        $classMetadata = $this->createMock(ClassMetadata::class);
        $reflectionClass = new \ReflectionClass($entity);
        $classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        $objectManager->method('getClassMetadata')
            ->willReturn($classMetadata)
        ;

        $listener->prePersistEntity($objectManager, $entity);
    }

    public function testPreUpdate(): void
    {
        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $listener = $this->createListener(propertyAccessor: $propertyAccessor);
        $listener->setClientIp('192.168.1.100');

        $entity = new class {
            #[UpdateIpColumn]
            private ?string $updateIp = null;

            public function getUpdateIp(): ?string
            {
                return $this->updateIp;
            }

            public function setUpdateIp(?string $updateIp): void
            {
                $this->updateIp = $updateIp;
            }
        };

        // 使用具体类进行Mock，原因：1）PreUpdateEventArgs是Doctrine ORM事件系统的核心类，2）需要特定的事件数据和参数，3）这是与Doctrine ORM的集成测试
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->method('getObject')
            ->willReturn($entity)
        ;

        $objectManager = $this->createMock(ObjectManager::class);
        $eventArgs->method('getObjectManager')
            ->willReturn($objectManager)
        ;

        $classMetadata = $this->createMock(ClassMetadata::class);
        $reflectionClass = new \ReflectionClass($entity);
        $classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        $objectManager->method('getClassMetadata')
            ->willReturn($classMetadata)
        ;

        // 设置PropertyAccessor的mock期望
        $propertyAccessor->expects($this->once())
            ->method('isWritable')
            ->with($entity, 'updateIp')
            ->willReturn(true)
        ;

        $propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($entity, 'updateIp', '192.168.1.100')
        ;

        $listener->preUpdate($eventArgs);
    }

    public function testPreUpdateEntity(): void
    {
        $propertyAccessor = $this->createMock(PropertyAccessor::class);
        $listener = $this->createListener(propertyAccessor: $propertyAccessor);
        $listener->setClientIp('10.10.10.10');

        $objectManager = $this->createMock(ObjectManager::class);
        $entity = new class {
            #[UpdateIpColumn]
            private ?string $updateIp = null;

            public function getUpdateIp(): ?string
            {
                return $this->updateIp;
            }

            public function setUpdateIp(?string $updateIp): void
            {
                $this->updateIp = $updateIp;
            }
        };

        // 使用具体类进行Mock，原因：1）PreUpdateEventArgs是Doctrine ORM事件系统的核心类，2）需要特定的事件数据和参数，3）这是与Doctrine ORM的集成测试
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);

        $propertyAccessor->expects($this->once())
            ->method('isWritable')
            ->with($entity, 'updateIp')
            ->willReturn(true)
        ;

        $propertyAccessor->expects($this->once())
            ->method('setValue')
            ->with($entity, 'updateIp', '10.10.10.10')
        ;

        $classMetadata = $this->createMock(ClassMetadata::class);
        $reflectionClass = new \ReflectionClass($entity);
        $classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        $objectManager->method('getClassMetadata')
            ->willReturn($classMetadata)
        ;

        $listener->preUpdateEntity($objectManager, $entity, $eventArgs);
    }
}
