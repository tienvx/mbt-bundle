<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertTitleCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertTitleCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertTitleCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Expected title';
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertTitleCommand
    {
        return new AssertTitleCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $actual, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->driver->expects($this->exactly(2))->method('getTitle')->willReturn($actual);
        $this->command->run('Welcome', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['Welcome', null],
            ['Goodbye', new Exception('Actual title "Goodbye" did not match "Welcome"')],
        ];
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
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
