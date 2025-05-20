<?php

namespace Tourze\DoctrineIpBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
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

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AutoconfigureTag('as-coroutine')]
class IpTrackListener implements ResetInterface, EntityCheckerInterface
{
    private ?string $clientIp = null;

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire(service: 'doctrine-ip.property-accessor')] private readonly PropertyAccessor $propertyAccessor,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        if (!$this->getClientIp()) {
            return;
        }
        $this->prePersistEntity($args->getObjectManager(), $args->getObject());
    }

    public function prePersistEntity(ObjectManager $objectManager, object $entity): void
    {
        if (!$this->getClientIp()) {
            return;
        }

        $reflection = $objectManager->getClassMetadata($entity::class)->getReflectionClass();
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            // 如果字段不可以写入，直接跳过即可
            if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
                continue;
            }

            if (empty($property->getAttributes(CreateIpColumn::class))) {
                continue;
            }

            // 已经有值了，我们就跳过
            $v = $property->getValue($entity);
            if (!empty($v)) {
                continue;
            }

            $ip = $this->getClientIp();
            $this->logger->debug("为{$property->getName()}分配创建时的IP", [
                'ip' => $ip,
            ]);
            $this->propertyAccessor->setValue($entity, $property->getName(), $ip);
        }
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if (!$this->getClientIp()) {
            return;
        }

        $entity = $args->getObject();
        $this->preUpdateEntity($args->getObjectManager(), $entity, $args);
    }

    public function preUpdateEntity(ObjectManager $objectManager, object $entity, PreUpdateEventArgs $eventArgs): void
    {
        if (!$this->getClientIp()) {
            return;
        }

        $reflection = $objectManager->getClassMetadata($entity::class)->getReflectionClass();
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            // 如果字段不可以写入，直接跳过即可
            if (!$this->propertyAccessor->isWritable($entity, $property->getName())) {
                continue;
            }

            if (empty($property->getAttributes(UpdateIpColumn::class))) {
                continue;
            }

            // 已经有值了，我们就跳过
            $v = $property->getValue($entity);
            if (!empty($v)) {
                continue;
            }

            $ip = $this->getClientIp();
            $this->logger->debug("为{$property->getName()}分配更新时的IP", [
                'ip' => $ip,
            ]);
            $this->propertyAccessor->setValue($entity, $property->getName(), $ip);
        }
    }

    public function reset(): void
    {
        $this->setClientIp(null);
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 4096)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $this->setClientIp($event->getRequest()->getClientIp());
    }
}
