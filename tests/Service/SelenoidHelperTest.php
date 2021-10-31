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

    public function testGetLogUrl(): void
    {
        $this->assertSame('http://localhost:4444/logs/607667f7e1c7923779e35506b040300d.log', $this->selenoidHelper->getLogUrl('607667f7e1c7923779e35506b040300d'));
    }

    public function testGetVideoUrl(): void
    {
        $this->assertSame('http://localhost:4444/video/607667f7e1c7923779e35506b040300d.mp4', $this->selenoidHelper->getVideoUrl('607667f7e1c7923779e35506b040300d'));
    }

    /**
     * @dataProvider capabilitiesParameterProvider
     */
    public function testGetCapabilities(bool $debug, ?int $bugId): void
    {
        $browser = new Browser();
        $browser->setName('firefox');
        $browser->setVersion('90.0');
        $task = new Task();
        $task->setId(123);
        $task->setBrowser($browser);
        $task->setDebug($debug);
        $this->assertCapabilities($this->selenoidHelper->getCapabilities($task, $bugId), $task, $bugId);
    }

    private function assertCapabilities(DesiredCapabilities $capabilities, Task $task, ?int $bugId = null): void
    {
        if ($bugId) {
            $this->assertTrue($capabilities->getCapability('enableVideo'));
            $this->assertTrue($capabilities->getCapability('enableLog'));
        } else {
            $this->assertSame($task->isDebug(), $capabilities->is('enableVideo'));
            $this->assertSame($task->isDebug(), $capabilities->is('enableLog'));
        }
        $this->assertSame($task->getBrowser()->getVersion(), $capabilities->getVersion());
        $this->assertSame($task->getBrowser()->getName(), $capabilities->getBrowserName());
        $this->assertFalse($capabilities->getCapability('enableVNC'));
    }

    public function capabilitiesParameterProvider(): array
    {
        return [
            [true, null],
            [true, 234],
            [false, null],
            [false, 234],
        ];
    }
}
