<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Tienvx\Bundle\MbtBundle\Command\Store\StoreTitleCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreTitleCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreTitleCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Variable to store page title';
    protected string $valueHelper = '';
    protected string $group = 'store';

    protected function createCommand(): StoreTitleCommand
    {
        return new StoreTitleCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with('title', 'Welcome');
        $this->driver->expects($this->once())->method('getTitle')->willReturn('Welcome');
        $this->command->run('title', null, $this->values, $this->driver);
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
