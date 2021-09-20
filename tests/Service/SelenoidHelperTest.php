<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\Browser;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelper;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 * @covers \Tienvx\Bundle\MbtBundle\Service\SelenoidHelper
 */
class SelenoidHelperTest extends StepsTestCase
{
    protected string $webdriverUri = 'http://localhost:4444';
    protected SelenoidHelperInterface $selenoidHelper;
    protected DesiredCapabilities $capabilities;

    protected function setUp(): void
    {
        $this->selenoidHelper = new SelenoidHelper();
        $this->selenoidHelper->setWebdriverUri($this->webdriverUri);
    }

    public function testGetVideoFileName(): void
    {
        $this->assertSame('bug-123.mp4', $this->selenoidHelper->getVideoFilename(123));
    }

    public function testGetVideoUrl(): void
    {
        $this->assertSame('http://localhost:4444/video/bug-123.mp4', $this->selenoidHelper->getVideoUrl(123));
    }

    public function testGetCapabilities(): void
    {
        $browser = new Browser();
        $browser->setName('firefox');
        $browser->setVersion('90.0');
        $task = new Task();
        $task->setId(123);
        $task->setBrowser($browser);
        $this->assertCapabilities($this->selenoidHelper->getCapabilities($task, 234), $task, 234);
        $this->assertCapabilities($this->selenoidHelper->getCapabilities($task), $task);
    }

    private function assertCapabilities(DesiredCapabilities $capabilities, Task $task, ?int $bugId = null): void
    {
        if ($bugId) {
            $this->assertTrue($capabilities->getCapability('enableVideo'));
            $this->assertSame($this->selenoidHelper->getVideoFilename(234), $capabilities->getCapability('videoName'));
            $this->assertSame('Recording video for bug 234', $capabilities->getCapability('name'));
        } else {
            $this->assertFalse($capabilities->is('enableVideo'));
            $this->assertFalse($capabilities->is('videoName'));
            $this->assertSame('Executing task 123', $capabilities->getCapability('name'));
        }
        $this->assertSame($task->getBrowser()->getVersion(), $capabilities->getVersion());
        $this->assertSame($task->getBrowser()->getName(), $capabilities->getBrowserName());
        $this->assertTrue($capabilities->getCapability('enableVNC'));
        $this->assertTrue($capabilities->getCapability('enableLog'));
    }
}
