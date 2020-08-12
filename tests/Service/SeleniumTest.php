<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
use Tienvx\Bundle\MbtBundle\Service\Selenium;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Selenium
 * @covers \Tienvx\Bundle\MbtBundle\Selenium\Helper
 */
class SeleniumTest extends TestCase
{
    protected ConfigLoaderInterface $configLoader;

    protected function setUp(): void
    {
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
    }

    public function testSetDsn(): void
    {
        $selenium = new Selenium($this->configLoader);
        $selenium->setDsn('http://localhost:4444/wd/hub');
        $reflection = new \ReflectionObject($selenium);
        $property = $reflection->getProperty('dsn');
        $property->setAccessible(true);
        $this->assertSame('http://localhost:4444/wd/hub', $property->getValue($selenium));
    }

    public function testCreateHelper(): void
    {
        $this->configLoader->expects($this->once())->method('getCapabilities')->willReturn([
            'browserName' => 'Chrome',
            'version' => '86',
        ]);
        /** @var Selenium|MockObject $selenium */
        $selenium = $this->getMockBuilder(Selenium::class)
            ->setConstructorArgs([$this->configLoader])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['createDriver'])
            ->getMock();
        $selenium->expects($this->once())->method('createDriver')->with('http://localhost:4444/wd/hub', [
            'browserName' => 'Chrome',
            'version' => '86',
        ])->willReturn($this->createMock(RemoteWebDriver::class));
        $selenium->setDsn('http://localhost:4444/wd/hub');
        $this->assertInstanceOf(Helper::class, $selenium->createHelper());
    }
}
