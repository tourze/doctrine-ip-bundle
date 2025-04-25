<?php

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;

class IpTrackListenerTest extends TestCase
{
    private LoggerInterface $logger;
    private PropertyAccessor $propertyAccessor;
    private IpTrackListener $listener;

    protected function setUp(): void
    {
        // 设置测试环境
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);
        $this->listener = new IpTrackListener(
            $this->logger,
            $this->propertyAccessor
        );
    }

    public function testImplementsInterfaces(): void
    {
        // 测试类实现了所需接口
        $this->assertInstanceOf(\Symfony\Contracts\Service\ResetInterface::class, $this->listener);
        $this->assertInstanceOf(EntityCheckerInterface::class, $this->listener);
    }

    public function testGetSetClientIp(): void
    {
        // 测试设置和获取客户端IP的功能
        $this->assertNull($this->listener->getClientIp());

        $testIp = '192.168.1.1';
        $this->listener->setClientIp($testIp);

        $this->assertEquals($testIp, $this->listener->getClientIp());
    }

    public function testReset(): void
    {
        // 测试reset()方法是否正确清除IP
        $this->listener->setClientIp('192.168.1.1');
        $this->listener->reset();

        $this->assertNull($this->listener->getClientIp());
    }

    public function testOnKernelRequest(): void
    {
        // 测试请求事件处理
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')
            ->willReturn('192.168.1.1');

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent->method('getRequest')
            ->willReturn($request);

        $this->listener->onKernelRequest($requestEvent);

        $this->assertEquals('192.168.1.1', $this->listener->getClientIp());
    }

    public function testPrePersistWithNoClientIp(): void
    {
        // 测试没有客户端IP时的行为 - 直接使用实体方法而不是event arg
        $this->listener->setClientIp(null);

        $objectManager = $this->createMock(ObjectManager::class);
        $entity = new class {
            #[CreateIpColumn]
            private ?string $createIp = null;

            public function getCreateIp(): ?string
            {
                return $this->createIp;
            }
        };

        // 直接调用实体处理方法，不使用final类的PrePersistEventArgs
        $this->listener->prePersistEntity($objectManager, $entity);

        $this->assertNull($entity->getCreateIp());
    }

    public function testDoctrineAttributesExist(): void
    {
        // 测试Doctrine事件监听器属性是否存在
        $reflection = new \ReflectionClass(IpTrackListener::class);

        $doctrineAttributes = $reflection->getAttributes(\Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener::class);
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

        $eventAttributes = $reflection->getAttributes(\Symfony\Component\EventDispatcher\Attribute\AsEventListener::class);
        $this->assertCount(1, $eventAttributes, 'onKernelRequest方法应该有一个AsEventListener属性');

        $instance = $eventAttributes[0]->newInstance();
        $this->assertEquals(KernelEvents::REQUEST, $instance->event, '应该监听KernelEvents::REQUEST事件');
        $this->assertEquals(4096, $instance->priority, '优先级应该是4096');
    }
}
