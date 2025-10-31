<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;

/**
 * @internal
 */
#[CoversClass(CreatedFromIpAware::class)]
final class CreatedFromIpAwareTest extends TestCase
{
    private TestEntityForCreatedFromIpAware $testClass;

    public function testGetCreatedFromIpWithDefaultValueReturnsNull(): void
    {
        $result = $this->testClass->getCreatedFromIp();

        $this->assertNull($result);
    }

    public function testSetCreatedFromIpWithValidIpv4SetsAndReturnsValue(): void
    {
        $ip = '192.168.1.1';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithValidIpv6SetsAndReturnsValue(): void
    {
        $ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithNullValueSetsNull(): void
    {
        // 先设置一个值，然后清空
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->testClass->setCreatedFromIp(null);

        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithEmptyStringSetsEmptyString(): void
    {
        $this->testClass->setCreatedFromIp('');

        $this->assertEquals('', $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithMaxLengthIpAddressSetsValue(): void
    {
        // 测试最大长度45字符的IP地址 (IPv6 最长可能的格式)
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:999';

        $this->testClass->setCreatedFromIp($longIp);

        $this->assertEquals($longIp, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithLocalhostIpv4SetsValue(): void
    {
        $ip = '127.0.0.1';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithLocalhostIpv6SetsValue(): void
    {
        $ip = '::1';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithPrivateNetworkIpSetsValue(): void
    {
        $ip = '10.0.0.1';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithCompressedIpv6SetsValue(): void
    {
        $ip = 'fe80::1%lo0';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpMultipleCallsOverrideValue(): void
    {
        $firstIp = '192.168.1.1';
        $secondIp = '10.0.0.1';

        $this->testClass->setCreatedFromIp($firstIp);
        $this->assertEquals($firstIp, $this->testClass->getCreatedFromIp());

        $this->testClass->setCreatedFromIp($secondIp);
        $this->assertEquals($secondIp, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithStringContainingSpacesSetsValue(): void
    {
        $ip = ' 192.168.1.1 ';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testPropertyIsolationIndependentFromOtherInstances(): void
    {
        $secondInstance = new TestEntityForCreatedFromIpAware();

        $firstIp = '192.168.1.1';
        $secondIp = '10.0.0.1';

        $this->testClass->setCreatedFromIp($firstIp);
        $secondInstance->setCreatedFromIp($secondIp);

        $this->assertEquals($firstIp, $this->testClass->getCreatedFromIp());
        $this->assertEquals($secondIp, $secondInstance->getCreatedFromIp());
    }

    public function testGetCreatedFromIpAfterSettingAndClearingReturnsNull(): void
    {
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->assertNotNull($this->testClass->getCreatedFromIp());

        $this->testClass->setCreatedFromIp(null);
        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithInvalidButAcceptableStringSetsValue(): void
    {
        // trait 不进行IP格式验证，接受任何字符串
        $invalidIp = 'not-an-ip-address';

        $this->testClass->setCreatedFromIp($invalidIp);

        $this->assertEquals($invalidIp, $this->testClass->getCreatedFromIp());
    }

    protected function setUp(): void
    {
        parent::setUp();
        // 创建一个使用 CreatedFromIpAware trait 的测试类实例
        $this->testClass = new TestEntityForCreatedFromIpAware();
    }
}
