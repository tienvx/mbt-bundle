<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Provider;

use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Provider\ProviderInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Provider\ProviderManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 */
class ProviderManagerTest extends TestCase
{
    protected ProviderManager $providerManager;
    protected ProviderInterface $provider;
    protected ServiceLocator $locator;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderInterface::class);
        $this->locator = $this->createMock(ServiceLocator::class);
        $plugins = ['selenoid'];
        $this->providerManager = new ProviderManager($this->locator, $plugins);
    }

    public function testGet(): void
    {
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('selenoid')->willReturn($this->provider);
        $this->assertSame($this->provider, $this->providerManager->get('selenoid'));
    }

    public function testAll(): void
    {
        $this->assertSame(['selenoid'], $this->providerManager->all());
    }

    public function testHasSelenoid(): void
    {
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->assertTrue($this->providerManager->has('selenoid'));
    }

    public function testDoesNotHaveOther(): void
    {
        $this->locator->expects($this->once())->method('has')->with('other')->willReturn(null);
        $this->assertFalse($this->providerManager->has('other'));
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provider "other" does not exist.');
        $this->providerManager->get('other');
    }

    public function testGetProvider(): void
    {
        $this->locator->expects($this->once())->method('has')->with('supported')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('supported')->willReturn($this->provider);
        $this->providerManager->setProviderName('supported');
        $this->assertSame($this->provider, $this->providerManager->getProvider());
    }

    public function testSetSeleniumServer(): void
    {
        $this->providerManager->setSeleniumServer('http://localhost:4444');
        $this->assertSame('http://localhost:4444', $this->providerManager->getSeleniumServer());
    }

    public function testSetAdminUrl(): void
    {
        $this->providerManager->setProviderName('selenoid');
        $this->assertSame('selenoid', $this->providerManager->getProviderName());
    }

    public function testCreateDriver(): void
    {
        $task = new Task();
        $bugId = 123;
        $capabilities = new DesiredCapabilities();
        $this->locator->expects($this->once())->method('has')->with('supported')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('supported')->willReturn($this->provider);
        $this->providerManager->setProviderName('supported');
        $this->providerManager->setSeleniumServer('http://localhost:4444');
        $this->provider
            ->expects($this->once())
            ->method('getSeleniumServerUrl')
            ->with('http://localhost:4444')
            ->willReturn('http://localhost:4444/wd/hub');
        $this->provider
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($task, $bugId)
            ->willReturn($capabilities);
        $this->expectException(WebDriverCurlException::class);
        $this->providerManager->createDriver($task, $bugId);
    }
}
