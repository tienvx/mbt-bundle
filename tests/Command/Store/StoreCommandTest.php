<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Tienvx\Bundle\MbtBundle\Command\Store\StoreCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = false;
    protected bool $isValueRequired = true;
    protected string $targetHelper = 'Value to store';
    protected string $valueHelper = 'Variable to store value';
    protected string $group = 'store';

    protected function createCommand(): StoreCommand
    {
        return new StoreCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with('count', '1');
        $this->command->run('1', 'count', $this->values, $this->driver);
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
            [null, false],
            ['', false],
            ['anything', true],
        ];
    }
}
