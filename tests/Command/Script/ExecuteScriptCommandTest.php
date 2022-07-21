<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Script;

use Tienvx\Bundle\MbtBundle\Command\Script\ExecuteScriptCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\ExecuteScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\AbstractScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class ExecuteScriptCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Script to run';
    protected string $valueHelper = 'Variable to set value (optional)';
    protected string $group = 'script';

    protected function createCommand(): ExecuteScriptCommand
    {
        return new ExecuteScriptCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(?string $value): void
    {
        $this->values->expects($this->never())->method('getValues');
        if ($value) {
            $this->values->expects($this->once())->method('setValue')->with($value, 3);
        } else {
            $this->values->expects($this->never())->method('setValue');
        }
        $this->driver->expects($this->once())->method('executeScript')->with(
            'return 2 + 1;',
            [],
        )->willReturn(3);
        $this->command->run('return 2 + 1;', $value, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [null],
            ['total'],
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
