<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Tienvx\Bundle\MbtBundle\Command\Store\StoreJsonCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreJsonCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreJsonCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = 'Encoded json';
    protected string $valueHelper = 'Variable to store json';
    protected string $group = 'store';

    protected function createCommand(): StoreJsonCommand
    {
        return new StoreJsonCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with(
            'json',
            $this->callback(fn ($object) => $object instanceof \stdClass && $object->items === [1, 2, 3])
        );
        $this->command->run('{ "items": [1, 2, 3] }', 'json', $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['{"key": "value"}', true],
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
