<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\WebDriverPlatform;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Provider\Selenoid;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Provider\Selenoid
 * @covers \Tienvx\Bundle\MbtBundle\Provider\AbstractProvider
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig
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

    public function testGetVideoFilename(): void
    {
        $selenoid = new Selenoid();
        $this->assertSame('bug-123.mp4', $selenoid->getVideoFilename(123));
    }

    public function testGetCapabilities(): void
    {
        $task = new Task();
        $task->setId(321);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setPlatform(WebDriverPlatform::VISTA);
        $seleniumConfig->setResolution('1024x768');
        $seleniumConfig->setBrowser(WebDriverBrowserType::CHROME);
        $seleniumConfig->setBrowserVersion('56.0');
        $task->setSeleniumConfig($seleniumConfig);
        $selenoid = new Selenoid();
        $capabilities = $selenoid->getCapabilities($task, 123);
        $this->assertInstanceOf(DesiredCapabilities::class, $capabilities);
        $this->assertSame([
            'enableVideo' => true,
            'videoName' => 'bug-123.mp4',
            'name' => 'Recording video for bug 123',
            'screenResolution' => '1024x768x24',
            'browserName' => 'chrome',
            'version' => '56.0',
            'platform' => 'VISTA',
            'enableVNC' => true,
            'enableLog' => true,
        ], $capabilities->toArray());
    }

    public function testGetMobileCapabilities(): void
    {
        $task = new Task();
        $task->setId(321);
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setPlatform(WebDriverPlatform::ANDROID);
        $seleniumConfig->setResolution('240x320');
        $seleniumConfig->setBrowser(WebDriverBrowserType::ANDROID);
        $seleniumConfig->setBrowserVersion('11.0');
        $task->setSeleniumConfig($seleniumConfig);
        $selenoid = new Selenoid();
        $capabilities = $selenoid->getCapabilities($task, 123);
        $this->assertInstanceOf(DesiredCapabilities::class, $capabilities);
        $this->assertSame([
            'enableVideo' => true,
            'videoName' => 'bug-123.mp4',
            'name' => 'Recording video for bug 123',
            'skin' => '240x320',
            'browserName' => 'android',
            'version' => '11.0',
            'platform' => 'ANDROID',
            'enableVNC' => true,
            'enableLog' => true,
        ], $capabilities->toArray());
    }
}
