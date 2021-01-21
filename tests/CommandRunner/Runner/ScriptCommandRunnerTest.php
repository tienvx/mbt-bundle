<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class ScriptCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new ScriptCommandRunner();
    }

    public function testRunScript(): void
    {
        $command = new Command();
        $command->setCommand(ScriptCommandRunner::RUN_SCRIPT);
        $command->setTarget('alert("Hello World!")');
        $this->color->expects($this->never())->method('getValues');
        $this->driver->expects($this->once())->method('executeScript')->with(
            'alert("Hello World!")',
            [],
        );
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testExecuteScript(): void
    {
        $command = new Command();
        $command->setCommand(ScriptCommandRunner::EXECUTE_SCRIPT);
        $command->setTarget('return 2 + 1;');
        $command->setValue('total');
        $this->color->expects($this->never())->method('getValues');
        $this->color->expects($this->once())->method('setValue')->with('total', 3);
        $this->driver->expects($this->once())->method('executeScript')->with(
            'return 2 + 1;',
            [],
        )->willReturn(3);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testExecuteAsyncScript(): void
    {
        $command = new Command();
        $command->setCommand(ScriptCommandRunner::EXECUTE_ASYNC_SCRIPT);
        $command->setTarget('window.setTimeout(function() { return "Hello";}, 1000);');
        $command->setValue('message');
        $this->color->expects($this->never())->method('getValues');
        $this->color->expects($this->once())->method('setValue')->with('message', 'Hello');
        $this->driver->expects($this->once())->method('executeAsyncScript')->with(
            'window.setTimeout(function() { return "Hello";}, 1000);',
            [],
        )->willReturn('Hello');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [ScriptCommandRunner::RUN_SCRIPT, 'anything', true],
        ];
    }
}
