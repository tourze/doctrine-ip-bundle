<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineIpBundle\DoctrineIpBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineIpBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineIpBundleTest extends AbstractBundleTestCase
{
}
