<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Provider;

use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Configuration;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Provider\ProviderInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Tests\Fixtures\Config;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Provider\ProviderManager
 * @covers \Tienvx\Bundle\MbtBundle\Plugin\AbstractPluginManager
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig
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
        $this->providerManager->setConfig(Config::DEFAULT_CONFIG[Configuration::PROVIDERS]);
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

    public function testCreateDriver(): void
    {
        $task = new Task();
        $task->getSeleniumConfig()->setProvider('selenoid');
        $bugId = 123;
        $capabilities = new DesiredCapabilities();
        $this->locator->expects($this->once())->method('has')->with('selenoid')->willReturn(true);
        $this->locator->expects($this->once())->method('get')->with('selenoid')->willReturn($this->provider);
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
        $this->expectException(WebDriverException::class);
        $this->providerManager->createDriver($task, $bugId);
    }

    public function testGetProviders(): void
    {
        $this->assertSame(['selenoid'], $this->providerManager->getProviders());
    }

    public function testSetSeleniumServer(): void
    {
        $this->assertSame('http://localhost:4444', $this->providerManager->getSeleniumServer('selenoid'));
    }

    public function testGetPlatforms(): void
    {
        $this->assertSame([
            WebDriverPlatform::LINUX,
            WebDriverPlatform::ANDROID,
        ], $this->providerManager->getPlatforms('selenoid'));
    }

    public function testGetBrowsers(): void
    {
        $this->assertSame([
            WebDriverBrowserType::CHROME,
            WebDriverBrowserType::FIREFOX,
            WebDriverBrowserType::MICROSOFT_EDGE,
            WebDriverBrowserType::OPERA,
        ], $this->providerManager->getBrowsers('selenoid', WebDriverPlatform::LINUX));
        $this->assertSame([
            WebDriverBrowserType::ANDROID,
            WebDriverBrowserType::CHROME,
        ], $this->providerManager->getBrowsers('selenoid', WebDriverPlatform::ANDROID));
    }

    public function testGetBrowserVersion(): void
    {
        $this->assertSame(['87.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::LINUX,
            WebDriverBrowserType::CHROME
        ));
        $this->assertSame(['83.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::LINUX,
            WebDriverBrowserType::FIREFOX
        ));
        $this->assertSame(['89.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::LINUX,
            WebDriverBrowserType::MICROSOFT_EDGE
        ));
        $this->assertSame(['72.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::LINUX,
            WebDriverBrowserType::OPERA
        ));
        $this->assertSame(['86.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::ANDROID,
            WebDriverBrowserType::CHROME
        ));
        $this->assertSame(['10.0'], $this->providerManager->getBrowserVersions(
            'selenoid',
            WebDriverPlatform::ANDROID,
            WebDriverBrowserType::ANDROID
        ));
    }

    public function testGetResolutions(): void
    {
        $this->assertSame([
            '1024x768',
            '1280x800',
            '1280x1024',
            '1366x768',
            '1440x900',
            '1680x1050',
            '1600x1200',
            '1920x1080',
            '2048x1536',
        ], $this->providerManager->getResolutions('selenoid', WebDriverPlatform::LINUX));
        $this->assertSame([
            '240x320',
            '240x400',
            '240x432',
            '320x480',
            '480x800',
            '480x854',
            '1024x600',
            '720x1280',
            '1280x800',
            '800x1280',
        ], $this->providerManager->getResolutions('selenoid', WebDriverPlatform::ANDROID));
    }
}
