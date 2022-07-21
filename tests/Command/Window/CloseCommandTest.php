<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Window;

use Tienvx\Bundle\MbtBundle\Command\Window\CloseCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\CloseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class CloseCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = false;
    protected bool $isValueRequired = false;
    protected string $targetHelper = '';
    protected string $valueHelper = '';
    protected string $group = 'window';

    protected function createCommand(): CloseCommand
    {
        return new CloseCommand();
    }

    public function testRun(): void
    {
        $this->driver->expects($this->once())->method('close');
        $this->command->run(null, null, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, true],
            ['', true],
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
