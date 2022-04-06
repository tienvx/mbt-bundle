<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\Browser;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelper;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;

/**
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 * @covers \Tienvx\Bundle\MbtBundle\Service\SelenoidHelper
 */
class SelenoidHelperTest extends StepsTestCase
{
    protected string $webdriverUri = 'http://localhost:4444';
    protected SelenoidHelperInterface $selenoidHelper;
    protected DesiredCapabilities $capabilities;
    protected TaskInterface $task;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->selenoidHelper = new SelenoidHelper();
        $this->selenoidHelper->setWebdriverUri($this->webdriverUri);
        $browser = new Browser();
        $browser->setName('firefox');
        $browser->setVersion('90.0');
        $this->task = new Task();
        $this->task->setId(123);
        $this->task->setBrowser($browser);
        $this->bug = new Bug();
        $this->bug->setId(234);
        $this->bug->setTask($this->task);
    }

    public function testGetLogUrl(): void
    {
        $this->assertSame(
            'http://localhost:4444/logs/task-123.log',
            $this->selenoidHelper->getLogUrl($this->task)
        );
        $this->assertSame(
            'http://localhost:4444/logs/bug-234.log',
            $this->selenoidHelper->getLogUrl($this->bug)
        );
    }

    public function testGetVideoUrl(): void
    {
        $this->assertSame(
            'http://localhost:4444/video/task-123.mp4',
            $this->selenoidHelper->getVideoUrl($this->task)
        );
        $this->assertSame(
            'http://localhost:4444/video/bug-234.mp4',
            $this->selenoidHelper->getVideoUrl($this->bug)
        );
    }

    /**
     * @dataProvider capabilitiesParameterProvider
     */
    public function testGetCapabilities(bool $debug, bool $hasBug): void
    {
        $this->assertCapabilities(
            $this->selenoidHelper->getCapabilities($hasBug ? $this->bug : $this->task, $debug),
            $debug,
            $hasBug
        );
    }

    private function assertCapabilities(DesiredCapabilities $capabilities, bool $debug, bool $hasBug): void
    {
        $this->assertSame($debug, $capabilities->is('enableVideo'));
        $this->assertSame($debug, $capabilities->is('enableLog'));
        $this->assertSame($this->task->getBrowser()->getVersion(), $capabilities->getVersion());
        $this->assertSame($this->task->getBrowser()->getName(), $capabilities->getBrowserName());
        $this->assertSame(
            $debug ? ($hasBug ? "bug-{$this->bug->getId()}.log" : "task-{$this->task->getId()}.log") : null,
            $capabilities->getCapability('logName')
        );
        $this->assertSame(
            $debug ? ($hasBug ? "bug-{$this->bug->getId()}.mp4" : "task-{$this->task->getId()}.mp4") : null,
            $capabilities->getCapability('videoName')
        );
    }

    public function capabilitiesParameterProvider(): array
    {
        return [
            [true, false],
            [true, true],
            [false, false],
            [false, true],
        ];
    }

    public function testGetLogName(): void
    {
        $this->assertSame(
            'task-123.log',
            $this->selenoidHelper->getLogName($this->task)
        );
        $this->assertSame(
            'bug-234.log',
            $this->selenoidHelper->getLogName($this->bug)
        );
    }

    public function testGetVideoName(): void
    {
        $this->assertSame(
            'task-123.mp4',
            $this->selenoidHelper->getVideoName($this->task)
        );
        $this->assertSame(
            'bug-234.mp4',
            $this->selenoidHelper->getVideoName($this->bug)
        );
    }
}
