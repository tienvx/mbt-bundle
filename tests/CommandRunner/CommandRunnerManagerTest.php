<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunnerManager
 */
class CommandRunnerManagerTest extends TestCase
{
    /**
     * @var MockObject[]
     */
    protected array $runners;
    protected CommandRunnerManager $manager;

    protected function setUp(): void
    {
        $this->runners = [
            $runner1 = $this->createMock(CommandRunner::class),
            $runner2 = $this->createMock(CommandRunner::class),
        ];
        $this->manager = new CommandRunnerManager($this->runners);
    }

    public function testGetActions(): void
    {
        $this->runners[0]->expects($this->once())->method('getActions')->willReturn([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
        ]);
        $this->runners[1]->expects($this->once())->method('getActions')->willReturn([
            'Action 3' => 'action3',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ], $this->manager->getActions());
    }

    public function testGetAssertions(): void
    {
        $this->runners[0]->expects($this->once())->method('getAssertions')->willReturn([
            'Assertion 1' => 'assertion1',
        ]);
        $this->runners[1]->expects($this->once())->method('getAssertions')->willReturn([
            'Assertion 2' => 'assertion2',
            'Assertion 3' => 'assertion3',
        ]);
        $this->assertSame([
            'Assertion 1' => 'assertion1',
            'Assertion 2' => 'assertion2',
            'Assertion 3' => 'assertion3',
        ], $this->manager->getAssertions());
    }

    public function testRunCommandInSecondRunner(): void
    {
        $command = $this->createMock(CommandInterface::class);
        $driver = $this->createMock(RemoteWebDriver::class);
        $this->runners[0]->expects($this->once())->method('supports')->willReturn(false);
        $this->runners[0]->expects($this->never())->method('run');
        $this->runners[1]->expects($this->once())->method('supports')->willReturn(true);
        $this->runners[1]->expects($this->once())->method('run')->with($command, $driver);
        $this->manager->run($command, $driver);
    }

    public function testRunCommandInFirstRunner(): void
    {
        $command = $this->createMock(CommandInterface::class);
        $driver = $this->createMock(RemoteWebDriver::class);
        $this->runners[0]->expects($this->once())->method('supports')->willReturn(true);
        $this->runners[0]->expects($this->once())->method('run')->with($command, $driver);
        $this->runners[1]->expects($this->never())->method('supports');
        $this->runners[1]->expects($this->never())->method('run');
        $this->manager->run($command, $driver);
    }
}
