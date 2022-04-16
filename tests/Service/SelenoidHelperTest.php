<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\Browser;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelper;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\SelenoidHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task\Browser
 * @uses \Tienvx\Bundle\MbtBundle\Model\Debug
 */
class SelenoidHelperTest extends TestCase
{
    protected string $webdriverUri = 'http://localhost:4444';
    protected SelenoidHelperInterface $selenoidHelper;
    protected DesiredCapabilities $capabilities;
    protected TaskInterface $task;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->selenoidHelper = $this->createPartialMock(
            SelenoidHelper::class,
            ['createDriverInternal', 'waitForVideoContainer']
        );
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
            $this->webdriverUri . '/logs/task-123.log',
            $this->selenoidHelper->getLogUrl($this->task)
        );
        $this->assertSame(
            $this->webdriverUri . '/logs/bug-234.log',
            $this->selenoidHelper->getLogUrl($this->bug)
        );
    }

    public function testGetVideoUrl(): void
    {
        $this->assertSame(
            $this->webdriverUri . '/video/task-123.mp4',
            $this->selenoidHelper->getVideoUrl($this->task)
        );
        $this->assertSame(
            $this->webdriverUri . '/video/bug-234.mp4',
            $this->selenoidHelper->getVideoUrl($this->bug)
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testCreateDriver(string $type, bool $debug): void
    {
        $this->{$type}->setDebug($debug);
        $driver = $this->createMock(RemoteWebDriver::class);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriverInternal')
            ->with(
                $this->webdriverUri . '/wd/hub',
                $this->callback(function (DesiredCapabilities $capabilities) use ($type, $debug) {
                    $this->assertCapabilities($capabilities, $type, $debug);

                    return true;
                })
            )
            ->willReturn($driver);
        $this->selenoidHelper->expects($this->exactly($debug))->method('waitForVideoContainer');
        $this->assertSame($driver, $this->selenoidHelper->createDriver($this->{$type}));
    }

    private function assertCapabilities(DesiredCapabilities $capabilities, string $type, bool $debug): void
    {
        $this->assertSame($debug, $capabilities->is('enableVideo'));
        $this->assertSame($debug, $capabilities->is('enableLog'));
        $this->assertSame($this->task->getBrowser()->getVersion(), $capabilities->getVersion());
        $this->assertSame($this->task->getBrowser()->getName(), $capabilities->getBrowserName());
        $this->assertSame(
            $debug ? "$type-{$this->{$type}->getId()}.log" : null,
            $capabilities->getCapability('logName')
        );
        $this->assertSame(
            $debug ? "$type-{$this->{$type}->getId()}.mp4" : null,
            $capabilities->getCapability('videoName')
        );
        $this->assertSame($debug ? 60 : null, $capabilities->getCapability('videoFrameRate'));
    }

    public function entityProvider(): array
    {
        return [
            ['task', false],
            ['task', true],
            ['bug', false],
            ['bug', true],
        ];
    }
}
