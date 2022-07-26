<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertValueCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertValueCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertValueCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Expected value';
    protected string $group = 'assert';

    protected function createCommand(): AssertValueCommand
    {
        return new AssertValueCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $actual, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->element
            ->expects($this->once())
            ->method('getAttribute')
            ->with('value')
            ->willReturn($actual);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'css selector' === $selector->getMechanism()
                    && '.quality' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('css=.quality', '14', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['14', null],
            ['15', new Exception('Actual value "15" did not match "14"')],
        ];
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['css=#selector', true],
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
