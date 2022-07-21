<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Window;

use Tienvx\Bundle\MbtBundle\Command\Window\OpenCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\OpenCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class OpenCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Url';
    protected string $valueHelper = '';
    protected string $group = 'window';

    protected function createCommand(): OpenCommand
    {
        return new OpenCommand();
    }

    public function testRun(): void
    {
        $this->driver->expects($this->once())->method('get')->with('https://demo.sylius.com/en_US/');
        $this->command->run('https://demo.sylius.com/en_US/', null, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['http://domain.example/path', true],
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
