<?php

namespace Tourze\DoctrineIpBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;

class CreatedFromIpAwareTest extends TestCase
{
    private object $testClass;

    public function test_getCreatedFromIp_withDefaultValue_returnsNull(): void
    {
        $result = $this->testClass->getCreatedFromIp();

        $this->assertNull($result);
    }

    public function test_setCreatedFromIp_withValidIpv4_setsAndReturnsValue(): void
    {
        $ip = '192.168.1.1';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withValidIpv6_setsAndReturnsValue(): void
    {
        $ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withNullValue_setsNull(): void
    {
        // 先设置一个值，然后清空
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $result = $this->testClass->setCreatedFromIp(null);

        $this->assertSame($this->testClass, $result);
        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withEmptyString_setsEmptyString(): void
    {
        $result = $this->testClass->setCreatedFromIp('');

        $this->assertSame($this->testClass, $result);
        $this->assertEquals('', $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withMaxLengthIpAddress_setsValue(): void
    {
        // 测试最大长度45字符的IP地址 (IPv6 最长可能的格式)
        $longIp = '2001:0db8:85a3:0000:0000:8a2e:0370:7334:999';

        $result = $this->testClass->setCreatedFromIp($longIp);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($longIp, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withLocalhostIpv4_setsValue(): void
    {
        $ip = '127.0.0.1';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withLocalhostIpv6_setsValue(): void
    {
        $ip = '::1';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withPrivateNetworkIp_setsValue(): void
    {
        $ip = '10.0.0.1';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withCompressedIpv6_setsValue(): void
    {
        $ip = 'fe80::1%lo0';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_returnsFluentInterface(): void
    {
        $result = $this->testClass->setCreatedFromIp('127.0.0.1');

        $this->assertInstanceOf(get_class($this->testClass), $result);
        $this->assertSame($this->testClass, $result);
    }

    public function test_setCreatedFromIp_multipleCallsOverrideValue(): void
    {
        $firstIp = '192.168.1.1';
        $secondIp = '10.0.0.1';

        $this->testClass->setCreatedFromIp($firstIp);
        $this->assertEquals($firstIp, $this->testClass->getCreatedFromIp());

        $this->testClass->setCreatedFromIp($secondIp);
        $this->assertEquals($secondIp, $this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withStringContainingSpaces_setsValue(): void
    {
        $ip = ' 192.168.1.1 ';

        $result = $this->testClass->setCreatedFromIp($ip);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($ip, $this->testClass->getCreatedFromIp());
    }

    public function test_propertyIsolation_independentFromOtherInstances(): void
    {
        $secondInstance = new class {
            use CreatedFromIpAware;
        };

        $firstIp = '192.168.1.1';
        $secondIp = '10.0.0.1';

        $this->testClass->setCreatedFromIp($firstIp);
        $secondInstance->setCreatedFromIp($secondIp);

        $this->assertEquals($firstIp, $this->testClass->getCreatedFromIp());
        $this->assertEquals($secondIp, $secondInstance->getCreatedFromIp());
    }

    public function test_getCreatedFromIp_afterSettingAndClearing_returnsNull(): void
    {
        $this->testClass->setCreatedFromIp('192.168.1.1');
        $this->assertNotNull($this->testClass->getCreatedFromIp());

        $this->testClass->setCreatedFromIp(null);
        $this->assertNull($this->testClass->getCreatedFromIp());
    }

    public function test_setCreatedFromIp_withInvalidButAcceptableString_setsValue(): void
    {
        // trait 不进行IP格式验证，接受任何字符串
        $invalidIp = 'not-an-ip-address';

        $result = $this->testClass->setCreatedFromIp($invalidIp);

        $this->assertSame($this->testClass, $result);
        $this->assertEquals($invalidIp, $this->testClass->getCreatedFromIp());
    }

    protected function setUp(): void
    {
        // 创建一个使用 CreatedFromIpAware trait 的匿名类
        $this->testClass = new class {
            use CreatedFromIpAware;
        };
    }
}
