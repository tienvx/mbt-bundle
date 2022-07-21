<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Window;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Tienvx\Bundle\MbtBundle\Command\Window\SelectWindowCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\SelectWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class SelectWindowCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Window handle e.g. A1B2-C3D4';
    protected string $valueHelper = '';
    protected string $group = 'window';

    protected function createCommand(): SelectWindowCommand
    {
        return new SelectWindowCommand();
    }

    public function testRun(): void
    {
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('window')->with('testing');
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->command->run('handle=testing', null, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['handle=', false],
            ['handle=ABC-123', true],
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
