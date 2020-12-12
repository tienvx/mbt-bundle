<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Provider\Selenoid;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Provider\Selenoid
 * @covers \Tienvx\Bundle\MbtBundle\Provider\AbstractProvider
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 */
class SelenoidTest extends TestCase
{
    public function testGetManager(): void
    {
        $this->assertSame(ProviderManager::class, Selenoid::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('selenoid', Selenoid::getName());
    }

    public function testGetSeleniumServerUrl(): void
    {
        $selenoid = new Selenoid();
        $this->assertSame('http://localhost:4444/wd/hub', $selenoid->getSeleniumServerUrl('http://localhost:4444'));
    }

    public function testGetVideoUrl(): void
    {
        $selenoid = new Selenoid();
        $this->assertSame(
            'http://localhost:4444/video/bug-123.mp4',
            $selenoid->getVideoUrl('http://localhost:4444', 123)
        );
    }

    public function testGetCapabilities(): void
    {
        $task = new Task();
        $task->setId(321);
        $task->setPlatform(WebDriverPlatform::VISTA);
        $task->setResolution('1024x768');
        $task->setBrowser(WebDriverBrowserType::CHROME);
        $task->setBrowserVersion('56.0');
        $selenoid = new Selenoid();
        $capabilities = $selenoid->getCapabilities($task, 123);
        $this->assertInstanceOf(DesiredCapabilities::class, $capabilities);
        $this->assertSame([
            'enableVideo' => true,
            'videoName' => 'bug-123.mp4',
            'name' => 'Recording video for bug 123',
            'screenResolution' => '1024x768x24',
            'browserName' => 'chrome',
            'version' => 'chrome',
            'platform' => 'VISTA',
            'enableVNC' => true,
            'enableLog' => true,
        ], $capabilities->toArray());
    }

    public function testGetMobileCapabilities(): void
    {
        $task = new Task();
        $task->setId(321);
        $task->setPlatform(WebDriverPlatform::ANDROID);
        $task->setResolution('240x320');
        $task->setBrowser(WebDriverBrowserType::ANDROID);
        $task->setBrowserVersion('11.0');
        $selenoid = new Selenoid();
        $capabilities = $selenoid->getCapabilities($task, 123);
        $this->assertInstanceOf(DesiredCapabilities::class, $capabilities);
        $this->assertSame([
            'enableVideo' => true,
            'videoName' => 'bug-123.mp4',
            'name' => 'Recording video for bug 123',
            'skin' => '240x320',
            'browserName' => 'android',
            'version' => 'android',
            'platform' => 'ANDROID',
            'enableVNC' => true,
            'enableLog' => true,
        ], $capabilities->toArray());
    }

    public function testGetOperatingSystems(): void
    {
        $this->assertSame([
            WebDriverPlatform::LINUX,
            WebDriverPlatform::ANDROID,
        ], Selenoid::getPlatforms());
    }

    public function testGetBrowsers(): void
    {
        $this->assertSame([
            WebDriverBrowserType::CHROME,
            WebDriverBrowserType::FIREFOX,
            WebDriverBrowserType::MICROSOFT_EDGE,
            WebDriverBrowserType::OPERA,
        ], array_keys(Selenoid::getBrowsers(WebDriverPlatform::LINUX)));
        $this->assertSame([
            WebDriverBrowserType::ANDROID,
            WebDriverBrowserType::CHROME,
        ], array_keys(Selenoid::getBrowsers(WebDriverPlatform::ANDROID)));
    }

    public function testGetResolutions(): void
    {
        $this->assertCount(9, Selenoid::getResolutions(WebDriverPlatform::LINUX));
        $this->assertCount(10, Selenoid::getResolutions(WebDriverPlatform::ANDROID));
    }
}
