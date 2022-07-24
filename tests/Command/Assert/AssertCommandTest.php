<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertCommand;
use Tienvx\Bundle\MbtBundle\Model\Values;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Values
 */
class AssertCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = 'Variable to get value from';
    protected string $valueHelper = 'Expected value';
    protected string $group = 'assert';

    protected function createCommand(): AssertCommand
    {
        return new AssertCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $target, string $value, ?Exception $exception): void
    {
        $values = new Values(['key1' => 'value1', 'key2' => 'value2']);
        if ($exception) {
            $this->expectExceptionObject($exception);
        } else {
            $this->expectNotToPerformAssertions();
        }
        $this->command->run($target, $value, $values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['key1', 'value1', null],
            ['key2', 'value3', new Exception('Actual value "value2" did not match "value3"')],
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
            [null, false],
            ['', true],
            ['anything', true],
        ];
    }
}
