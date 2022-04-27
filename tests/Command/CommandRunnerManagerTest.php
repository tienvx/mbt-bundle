<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Command\CommandPreprocessorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerManager;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

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
    protected CommandInterface $command;
    protected CommandInterface $processedCommand;
    protected RemoteWebDriver $driver;
    protected ValuesInterface $values;

    protected function setUp(): void
    {
        $this->runners = [
            $runner1 = $this->createMock(CommandRunnerInterface::class),
            $runner2 = $this->createMock(CommandRunnerInterface::class),
            $runner3 = $this->createMock(CommandRunnerInterface::class),
        ];
        $this->preprocessor = $this->createMock(CommandPreprocessorInterface::class);
        $this->manager = new CommandRunnerManager($this->runners, $this->preprocessor);
        $this->command = $this->createMock(CommandInterface::class);
        $this->processedCommand = $this->createMock(CommandInterface::class);
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->values = $this->createMock(ValuesInterface::class);
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
        $this->runners[2]->expects($this->once())->method('getAllCommands')->willReturn([
            'Action 4' => 'action4',
            'Action 5' => 'action5',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
            'Action 4' => 'action4',
            'Action 5' => 'action5',
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
        $this->runners[2]->expects($this->once())->method('getCommandsRequireTarget')->willReturn([
            'Action 4' => 'action4',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
            'Action 4' => 'action4',
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
        $this->runners[2]->expects($this->once())->method('getCommandsRequireValue')->willReturn([
            'Action 5' => 'action5',
        ]);
        $this->assertSame([
            'Action 1' => 'action1',
            'Action 4' => 'action4',
            'Action 2' => 'action2',
            'Action 3' => 'action3',
            'Action 5' => 'action5',
        ], $this->manager->getCommandsRequireValue());
    }

    /**
     * @testWith [0]
     *           [1]
     *           [2]
     */
    public function testRunCommand(int $support): void
    {
        foreach ($this->runners as $index => $runner) {
            if ($index < $support) {
                $runner->expects($this->once())->method('supports')->willReturn(false);
                $runner->expects($this->never())->method('run');
            } elseif ($index === $support) {
                $runner->expects($this->once())->method('supports')->willReturn(true);
                $runner
                    ->expects($this->once())
                    ->method('run')
                    ->with($this->processedCommand, $this->values, $this->driver);
            } else {
                $runner->expects($this->never())->method('supports');
                $runner->expects($this->never())->method('run');
            }
        }
        $this->preprocessor
            ->expects($this->once())
            ->method('process')
            ->with($this->command, $this->values)
            ->willReturn($this->processedCommand);
        $this->manager->run($this->command, $this->values, $this->driver);
    }
}
