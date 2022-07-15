<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\ScriptCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
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
        $this->values->expects($this->never())->method('getValues');
        $this->driver->expects($this->once())->method('executeScript')->with(
            'alert("Hello World!")',
            [],
        );
        $this->runner->run($command, $this->values, $this->driver);
    }

    public function testExecuteScript(): void
    {
        $command = new Command();
        $command->setCommand(ScriptCommandRunner::EXECUTE_SCRIPT);
        $command->setTarget('return 2 + 1;');
        $command->setValue('total');
        $this->values->expects($this->never())->method('getValues');
        $this->values->expects($this->once())->method('setValue')->with('total', 3);
        $this->driver->expects($this->once())->method('executeScript')->with(
            'return 2 + 1;',
            [],
        )->willReturn(3);
        $this->runner->run($command, $this->values, $this->driver);
    }

    public function testExecuteAsyncScript(): void
    {
        $command = new Command();
        $command->setCommand(ScriptCommandRunner::EXECUTE_ASYNC_SCRIPT);
        $command->setTarget('window.setTimeout(function() { return "Hello";}, 1000);');
        $command->setValue('message');
        $this->values->expects($this->never())->method('getValues');
        $this->values->expects($this->once())->method('setValue')->with('message', 'Hello');
        $this->driver->expects($this->once())->method('executeAsyncScript')->with(
            'window.setTimeout(function() { return "Hello";}, 1000);',
            [],
        )->willReturn('Hello');
        $this->runner->run($command, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [ScriptCommandRunner::RUN_SCRIPT, 'anything', true],
        ];
    }

    public function commandsRequireTarget(): array
    {
        return [
            ScriptCommandRunner::RUN_SCRIPT,
            ScriptCommandRunner::EXECUTE_SCRIPT,
            ScriptCommandRunner::EXECUTE_ASYNC_SCRIPT,
        ];
    }

    public function commandsRequireValue(): array
    {
        return [
            ScriptCommandRunner::EXECUTE_SCRIPT,
            ScriptCommandRunner::EXECUTE_ASYNC_SCRIPT,
        ];
    }
}
