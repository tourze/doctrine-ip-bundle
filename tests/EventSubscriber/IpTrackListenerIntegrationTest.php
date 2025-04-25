<?php

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tourze\DoctrineIpBundle\EventSubscriber\IpTrackListener;
use Tourze\DoctrineIpBundle\Tests\Entity\TestEntity;

class IpTrackListenerIntegrationTest extends TestCase
{
    private IpTrackListener $listener;
    private LoggerInterface $logger;
    private PropertyAccessor $propertyAccessor;
    private ObjectManager $objectManager;
    private ClassMetadata $classMetadata;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessor::class);

        // 配置PropertyAccessor模拟对象
        $this->propertyAccessor->method('isWritable')
            ->willReturn(true);

        $this->listener = new IpTrackListener(
            $this->logger,
            $this->propertyAccessor
        );

        // 设置客户端IP
        $this->listener->setClientIp('192.168.10.1');

        // 设置模拟的ObjectManager
        $reflection = new \ReflectionClass(TestEntity::class);

        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflection);

        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->objectManager->method('getClassMetadata')
            ->with(TestEntity::class)
            ->willReturn($this->classMetadata);
    }

    public function testPrePersistWithEntity(): void
    {
        $entity = new TestEntity();

        // 测试前记录原始值
        $originalCreateIp = $entity->getCreateIp();

        // 配置PropertyAccessor模拟对象，模拟设置值
        $this->propertyAccessor->method('setValue')
            ->willReturnCallback(function ($obj, $property, $value) {
                if ($obj instanceof TestEntity && $property === 'createIp') {
                    $obj->setCreateIp($value);
                }
            });

        // 执行测试
        $this->listener->prePersistEntity($this->objectManager, $entity);

        // 验证结果 - 手动检查值是否已被设置
        $this->assertNotEquals($originalCreateIp, $entity->getCreateIp());
        $this->assertEquals('192.168.10.1', $entity->getCreateIp());
    }

    public function testPreUpdateWithEntity(): void
    {
        $entity = new TestEntity();

        // 测试前记录原始值
        $originalUpdateIp = $entity->getUpdateIp();

        // 配置PropertyAccessor模拟对象，模拟设置值
        $this->propertyAccessor->method('setValue')
            ->willReturnCallback(function ($obj, $property, $value) {
                if ($obj instanceof TestEntity && $property === 'updateIp') {
                    $obj->setUpdateIp($value);
                }
            });

        $args = $this->createMock(PreUpdateEventArgs::class);

        // 执行测试
        $this->listener->preUpdateEntity($this->objectManager, $entity, $args);

        // 验证结果 - 手动检查值是否已被设置
        $this->assertNotEquals($originalUpdateIp, $entity->getUpdateIp());
        $this->assertEquals('192.168.10.1', $entity->getUpdateIp());
    }

    public function testPrePersistWithNullIp(): void
    {
        $entity = new TestEntity();

        // 记录原始值
        $originalCreateIp = $entity->getCreateIp();

        // 重置客户端IP为null
        $this->listener->setClientIp(null);

        // 配置PropertyAccessor模拟对象 - 不应该被调用
        $this->propertyAccessor->method('setValue')
            ->willReturnCallback(function ($obj, $property, $value) {
                // 如果这个方法被调用了，我们需要让测试失败
                $this->fail('setValue方法不应该被调用，因为IP为null');
            });

        // 执行测试
        $this->listener->prePersistEntity($this->objectManager, $entity);

        // 验证结果 - 值应该保持不变
        $this->assertEquals($originalCreateIp, $entity->getCreateIp());
        $this->assertNull($entity->getCreateIp());
    }
}
