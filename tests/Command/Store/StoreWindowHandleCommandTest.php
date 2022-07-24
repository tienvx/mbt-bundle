<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Tienvx\Bundle\MbtBundle\Command\Store\StoreWindowHandleCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreWindowHandleCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreWindowHandleCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Variable to store window handle';
    protected string $valueHelper = '';
    protected string $group = 'store';

    protected function createCommand(): StoreWindowHandleCommand
    {
        return new StoreWindowHandleCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with('windowHandle', 'window-123');
        $this->driver->expects($this->once())->method('getWindowHandle')->willReturn('window-123');
        $this->command->run('windowHandle', null, $this->values, $this->driver);
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
