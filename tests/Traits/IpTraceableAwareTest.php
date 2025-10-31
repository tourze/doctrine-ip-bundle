<?php

declare(strict_types=1);

namespace Tourze\DoctrineIpBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;

/**
 * @internal
 */
#[CoversClass(IpTraceableAware::class)]
final class IpTraceableAwareTest extends TestCase
{
    private TestEntityForIpTraceableAware $testClass;

    public function testGetCreatedFromIpWithDefaultValueReturnsNull(): void
    {
        $result = $this->testClass->getCreatedFromIp();

        $this->assertNull($result);
    }

    public function testGetUpdatedFromIpWithDefaultValueReturnsNull(): void
    {
        $result = $this->testClass->getUpdatedFromIp();

        $this->assertNull($result);
    }

    public function testSetCreatedFromIpWithValidIpSetsAndReturnsValue(): void
    {
        $ip = '192.168.1.1';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithIpv6SetsAndReturnsValue(): void
    {
        $ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithNullValueSetsNull(): void
    {
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->testClass->setCreatedFromIp(null);

        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function testSetCreatedFromIpWithEmptyStringSetsEmptyString(): void
    {
        $this->testClass->setCreatedFromIp('');

        $this->assertEquals('', $this->testClass->getCreatedFromIp());
    }

    public function testSetUpdatedFromIpWithValidIpSetsAndReturnsValue(): void
    {
        $ip = '10.0.0.1';

        $this->testClass->setUpdatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getUpdatedFromIp());
    }

    public function testSetUpdatedFromIpWithIpv6SetsAndReturnsValue(): void
    {
        $ip = 'fe80::1%lo0';

        $this->testClass->setUpdatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getUpdatedFromIp());
    }

    public function testSetUpdatedFromIpWithNullValueSetsNull(): void
    {
        $this->testClass->setUpdatedFromIp('192.168.1.1');
        $this->testClass->setUpdatedFromIp(null);

        $this->assertNull($this->testClass->getUpdatedFromIp());
    }

    public function testSetUpdatedFromIpWithEmptyStringSetsEmptyString(): void
    {
        $this->testClass->setUpdatedFromIp('');

        $this->assertEquals('', $this->testClass->getUpdatedFromIp());
    }

    public function testSetCreatedFromIpWithLongIpAddressSetsValue(): void
    {
        // 测试最大长度45字符的IP地址
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:9999';

        $this->testClass->setCreatedFromIp($longIp);

        $this->assertEquals($longIp, $this->testClass->getCreatedFromIp());
    }

    public function testSetUpdatedFromIpWithLongIpAddressSetsValue(): void
    {
        // 测试最大长度45字符的IP地址
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:9999';

        $this->testClass->setUpdatedFromIp($longIp);

        $this->assertEquals($longIp, $this->testClass->getUpdatedFromIp());
    }

    public function testChainedSettersWorkCorrectly(): void
    {
        $createdIp = '192.168.1.100';
        $updatedIp = '192.168.1.200';

        $this->testClass->setCreatedFromIp($createdIp);
        $this->testClass->setUpdatedFromIp($updatedIp);

        $this->assertEquals($createdIp, $this->testClass->getCreatedFromIp());
        $this->assertEquals($updatedIp, $this->testClass->getUpdatedFromIp());
    }

    public function testIndependentPropertiesStoreValuesIndependently(): void
    {
        $createdIp = '10.0.0.1';
        $updatedIp = '172.16.0.1';

        $this->testClass->setCreatedFromIp($createdIp);
        $this->testClass->setUpdatedFromIp($updatedIp);

        $this->assertEquals($createdIp, $this->testClass->getCreatedFromIp());
        $this->assertEquals($updatedIp, $this->testClass->getUpdatedFromIp());

        // 修改一个属性不应影响另一个
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->assertEquals('192.168.1.1', $this->testClass->getCreatedFromIp());
        $this->assertEquals($updatedIp, $this->testClass->getUpdatedFromIp());
    }

    protected function setUp(): void
    {
        parent::setUp();
        // 创建一个使用 IpTraceableAware trait 的测试类实例
        $this->testClass = new TestEntityForIpTraceableAware();
    }
}
