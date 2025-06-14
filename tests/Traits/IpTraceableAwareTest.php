<?php

namespace Tourze\DoctrineIpBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;

class IpTraceableAwareTest extends TestCase
{
    private object $testClass;

    public function test_getCreatedFromIp_withDefaultValue_returnsNull(): void
    {
        $result = $this->testClass->getCreatedFromIp();

        $this->assertNull($result);
    }

    public function test_getUpdatedFromIp_withDefaultValue_returnsNull(): void
    {
        $result = $this->testClass->getUpdatedFromIp();

        $this->assertNull($result);
    }

    public function test_setCreatedFromIp_withValidIp_setsAndReturnsValue(): void
    {
        $ip = '192.168.1.1';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withIpv6_setsAndReturnsValue(): void
    {
        $ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $this->testClass->setCreatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withNullValue_setsNull(): void
    {
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->testClass->setCreatedFromIp(null);

        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withEmptyString_setsEmptyString(): void
    {
        $this->testClass->setCreatedFromIp('');

        $this->assertEquals('', $this->testClass->getCreatedFromIp());
    }

    public function test_setUpdatedFromIp_withValidIp_setsAndReturnsValue(): void
    {
        $ip = '10.0.0.1';

        $result = $this->testClass->setUpdatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getUpdatedFromIp());
    }

    public function test_setUpdatedFromIp_withIpv6_setsAndReturnsValue(): void
    {
        $ip = 'fe80::1%lo0';

        $this->testClass->setUpdatedFromIp($ip);

        $this->assertEquals($ip, $this->testClass->getUpdatedFromIp());
    }

    public function test_setUpdatedFromIp_withNullValue_setsNull(): void
    {
        $this->testClass->setUpdatedFromIp('192.168.1.1');
        $this->testClass->setUpdatedFromIp(null);

        $this->assertNull($this->testClass->getUpdatedFromIp());
    }

    public function test_setUpdatedFromIp_withEmptyString_setsEmptyString(): void
    {
        $this->testClass->setUpdatedFromIp('');

        $this->assertEquals('', $this->testClass->getUpdatedFromIp());
    }

    public function test_setCreatedFromIp_withLongIpAddress_setsValue(): void
    {
        // 测试最大长度45字符的IP地址
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:9999';

        $this->testClass->setCreatedFromIp($longIp);

        $this->assertEquals($longIp, $this->testClass->getCreatedFromIp());
    }

    public function test_setUpdatedFromIp_withLongIpAddress_setsValue(): void
    {
        // 测试最大长度45字符的IP地址
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:9999';

        $this->testClass->setUpdatedFromIp($longIp);

        $this->assertEquals($longIp, $this->testClass->getUpdatedFromIp());
    }

    public function test_setCreatedFromIp_returnsFluentInterface(): void
    {
        $result = $this->testClass->setCreatedFromIp('127.0.0.1');

        $this->assertInstanceOf(get_class($this->testClass), $result);
        $this->assertSame($this->testClass, $result);
    }

    public function test_setUpdatedFromIp_returnsFluentInterface(): void
    {
        $result = $this->testClass->setUpdatedFromIp('127.0.0.1');

        $this->assertInstanceOf(get_class($this->testClass), $result);
        $this->assertSame($this->testClass, $result);
    }

    public function test_chainedSetters_workCorrectly(): void
    {
        $createdIp = '192.168.1.100';
        $updatedIp = '192.168.1.200';

        $result = $this->testClass
            ->setCreatedFromIp($createdIp)
            ->setUpdatedFromIp($updatedIp);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($createdIp, $this->testClass->getCreatedFromIp());
        $this->assertEquals($updatedIp, $this->testClass->getUpdatedFromIp());
    }

    public function test_independentProperties_storeValuesIndependently(): void
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
        // 创建一个使用 IpTraceableAware trait 的匿名类
        $this->testClass = new class {
            use IpTraceableAware;
        };
    }
}
