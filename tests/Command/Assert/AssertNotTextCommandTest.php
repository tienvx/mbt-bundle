<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotTextCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotTextCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertNotTextCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Unexpected text';
    protected string $group = 'assert';

    protected function createCommand(): AssertNotTextCommand
    {
        return new AssertNotTextCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $actual, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->element->expects($this->once())->method('getText')->willReturn($actual);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'xpath' === $selector->getMechanism()
                    && '//h4[@href="#"]' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('xpath=//h4[@href="#"]', 'Welcome to our store', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['Goodbye! See you again', null],
            [
                'Welcome to our store',
                new Exception('Actual text "Welcome to our store" did match "Welcome to our store"'),
            ],
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
