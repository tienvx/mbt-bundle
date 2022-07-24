<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Script;

use Tienvx\Bundle\MbtBundle\Command\Script\ExecuteAsyncScriptCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\ExecuteAsyncScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\AbstractScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class ExecuteAsyncScriptCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Async script to run';
    protected string $valueHelper = 'Variable to set value (optional)';
    protected string $group = 'script';

    protected function createCommand(): ExecuteAsyncScriptCommand
    {
        return new ExecuteAsyncScriptCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(?string $value): void
    {
        $this->values->expects($this->never())->method('getValues');
        if ($value) {
            $this->values->expects($this->once())->method('setValue')->with($value, 'Hello');
        } else {
            $this->values->expects($this->never())->method('setValue');
        }
        $this->driver->expects($this->once())->method('executeAsyncScript')->with(
            'window.setTimeout(function() { return "Hello";}, 1000);',
            [],
        )->willReturn('Hello');
        $this->command->run(
            'window.setTimeout(function() { return "Hello";}, 1000);',
            $value,
            $this->values,
            $this->driver,
        );
    }

    public function runProvider(): array
    {
        return [
            [null],
            ['message'],
        ];
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', true],
        ];
    }

    public function valueProvider(): array
    {
        return [
            [null, true],
            ['', true],
            ['anything', true],
        ];
    }
}
