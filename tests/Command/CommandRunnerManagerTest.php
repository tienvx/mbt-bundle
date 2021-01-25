<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager
 */
class CommandRunnerManagerTest extends TestCase
{
    /**
     * @var MockObject[]
     */
    protected array $runners;
    protected CommandPreprocessorInterface $preprocessor;
    protected CommandRunnerManager $manager;

    protected function setUp(): void
    {
        $this->runners = [
            $runner1 = $this->createMock(CommandRunner::class),
            $runner2 = $this->createMock(CommandRunner::class),
        ];
        $this->preprocessor = $this->createMock(CommandPreprocessorInterface::class);
        $this->manager = new CommandRunnerManager($this->runners, $this->preprocessor);
    }

    public function testGetAllCommands(): void
    {
        $this->runners[0]->expects($this->once())->method('getAllCommands')->willReturn([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
        ]);
        $this->runners[1]->expects($this->once())->method('getAllCommands')->willReturn([
            'Action 3' => 'action3',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ], $this->manager->getAllCommands());
    }

    public function testGetCommandsRequireTarget(): void
    {
        $this->runners[0]->expects($this->once())->method('getCommandsRequireTarget')->willReturn([
            'Action 1' => 'action1',
        ]);
        $this->runners[1]->expects($this->once())->method('getCommandsRequireTarget')->willReturn([
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ], $this->manager->getCommandsRequireTarget());
    }

    public function testGetCommandsRequireValue(): void
    {
        $this->runners[0]->expects($this->once())->method('getCommandsRequireValue')->willReturn([
            'Action 1' => 'action1',
            'Action 4' => 'action4',
        ]);
        $this->runners[1]->expects($this->once())->method('getCommandsRequireValue')->willReturn([
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 4' => 'action4',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
        ], $this->manager->getCommandsRequireValue());
    }

    public function testRunCommandInSecondRunner(): void
    {
        $command = $this->createMock(CommandInterface::class);
        $newCommand = $this->createMock(CommandInterface::class);
        $driver = $this->createMock(RemoteWebDriver::class);
        $color = $this->createMock(ColorInterface::class);
        $this->runners[0]->expects($this->once())->method('supports')->willReturn(false);
        $this->runners[0]->expects($this->never())->method('run');
        $this->runners[1]->expects($this->once())->method('supports')->willReturn(true);
        $this->runners[1]->expects($this->once())->method('run')->with($newCommand, $color, $driver);
        $this->preprocessor->expects($this->once())->method('process')->with($command, $color)->willReturn($newCommand);
        $this->manager->run($command, $color, $driver);
    }

    public function testRunCommandInFirstRunner(): void
    {
        $command = $this->createMock(CommandInterface::class);
        $newCommand = $this->createMock(CommandInterface::class);
        $driver = $this->createMock(RemoteWebDriver::class);
        $color = $this->createMock(ColorInterface::class);
        $this->runners[0]->expects($this->once())->method('supports')->willReturn(true);
        $this->runners[0]->expects($this->once())->method('run')->with($newCommand, $color, $driver);
        $this->runners[1]->expects($this->never())->method('supports');
        $this->runners[1]->expects($this->never())->method('run');
        $this->preprocessor->expects($this->once())->method('process')->with($command, $color)->willReturn($newCommand);
        $this->manager->run($command, $color, $driver);
    }
}
