<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Script;

use Tienvx\Bundle\MbtBundle\Command\Script\RunScriptCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\RunScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Script\AbstractScriptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class RunScriptCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Script to run';
    protected string $valueHelper = '';
    protected string $group = 'script';

    protected function createCommand(): RunScriptCommand
    {
        return new RunScriptCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->never())->method('getValues');
        $this->driver->expects($this->once())->method('executeScript')->with(
            'alert("Hello World!")',
            [],
        );
        $this->command->run('alert("Hello World!")', null, $this->values, $this->driver);
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
