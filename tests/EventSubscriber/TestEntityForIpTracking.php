<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\EventSubscriber;

use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;

/**
 * @internal
 */
class TestEntityForIpTracking
{
    use IpTraceableAware;
}
