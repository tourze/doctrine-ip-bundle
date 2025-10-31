<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Service\ResetInterface;
use Tourze\DoctrineEntityCheckerBundle\Checker\EntityCheckerInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;

/**
 * Doctrine IP跟踪监听器.
 *
 * 自动跟踪和记录实体创建和更新时的客户端IP地址。
 * 支持PHP 8.1属性配置，与Symfony和Doctrine无缝集成。
 *
 * 功能特性：
 * - 自动捕获客户端IP地址
 * - 支持私有属性注入
 * - 线程安全，支持请求隔离
 * - 零配置，开箱即用
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AutoconfigureTag(name: 'as-coroutine')]
#[WithMonologChannel(channel: 'doctrine_ip')]
class IpTrackListener implements ResetInterface, EntityCheckerInterface
{
    private ?string $clientIp = null;

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'doctrine-ip.property-accessor')] private readonly PropertyAccessor $propertyAccessor,
    ) {
    }

    /**
     * Doctrine prePersist事件处理器.
     *
     * @param PrePersistEventArgs $args Doctrine prePersist事件参数
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        if (null === $this->getClientIp()) {
            return;
        }
        $this->prePersistEntity($args->getObjectManager(), $args->getObject());
    }

    /**
     * 为实体设置创建时IP.
     *
     * @param ObjectManager $objectManager Doctrine对象管理器
     * @param object        $entity         目标实体对象
     */
    public function prePersistEntity(ObjectManager $objectManager, object $entity): void
    {
        $this->setIpForEntity($objectManager, $entity, CreateIpColumn::class, '创建时的IP');
    }

    /**
     * 获取当前客户端IP地址.
     *
     * @return string|null 当前客户端IP地址，如果未设置则返回null
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * 设置客户端IP地址.
     *
     * @param string|null $clientIp 客户端IP地址
     */
    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    /**
     * Doctrine preUpdate事件处理器.
     *
     * @param PreUpdateEventArgs $args Doctrine preUpdate事件参数
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if (null === $this->getClientIp()) {
            return;
        }

        $entity = $args->getObject();
        $this->preUpdateEntity($args->getObjectManager(), $entity, $args);
    }

    /**
     * 为实体设置更新时IP.
     *
     * @param ObjectManager      $objectManager Doctrine对象管理器
     * @param object             $entity       目标实体对象
     * @param PreUpdateEventArgs $eventArgs    Doctrine preUpdate事件参数
     */
    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        $this->setIpForEntity($objectManager, $entity, UpdateIpColumn::class, '更新时的IP');
    }

    /**
     * 为实体设置IP字段的通用方法.
     *
     * @param ObjectManager $objectManager   Doctrine对象管理器
     * @param object        $entity         目标实体对象
     * @param string        $attributeClass IP列属性类名
     * @param string        $logMessage     日志消息
     */
    private function setIpForEntity(ObjectManager $objectManager, object $entity, string $attributeClass, string $logMessage): void
    {
        $clientIp = $this->getClientIp();
        if (null === $clientIp || '' === $clientIp) {
            return;
        }

        $reflection = $objectManager->getClassMetadata($entity::class)->getReflectionClass();
        foreach ($reflection->getProperties() as $property) {
            if (!$this->isEligibleProperty($entity, $property, $attributeClass)) {
                continue;
            }

            if (!$this->isPropertyEmpty($entity, $property)) {
                continue;
            }

            $this->logger->debug("为{$property->getName()}分配{$logMessage}", [
                'ip' => $clientIp,
            ]);
            $this->propertyAccessor->setValue($entity, $property->getName(), $clientIp);
        }
    }

    private function isEligibleProperty(object $entity, \ReflectionProperty $property, string $attributeClass): bool
    {
        if ($property->isStatic()) {
            return false;
        }

        if ([] === $property->getAttributes($attributeClass)) {
            return false;
        }

        if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
            return false;
        }

        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        return true;
    }

    private function isPropertyEmpty(object $entity, \ReflectionProperty $property): bool
    {
        if (!$property->isInitialized($entity)) {
            return true;
        }

        $value = $property->getValue($entity);

        return null === $value || '' === $value;
    }

    /**
     * 重置监听器状态，清除缓存的IP地址.
     *
     * 实现ResetInterface接口，确保每个请求之间IP地址的隔离
     */
    public function reset(): void
    {
        $this->setClientIp(null);
    }

    /**
     * Symfony内核请求事件处理器.
     *
     * 在每个请求开始时捕获客户端IP地址并缓存
     *
     * @param RequestEvent $event Symfony内核请求事件
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 4096)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $this->setClientIp($event->getRequest()->getClientIp());
    }
}
