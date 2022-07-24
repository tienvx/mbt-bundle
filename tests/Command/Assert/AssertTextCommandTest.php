<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertTextCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertTextCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertTextCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Expected text';
    protected string $group = 'assert';

    protected function createCommand(): AssertTextCommand
    {
        return new AssertTextCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $actual, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn($actual);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'xpath' === $selector->getMechanism()
                    && '//h4[@href="#"]' === $selector->getValue();
            }))
            ->willReturn($element);
        $this->command->run('xpath=//h4[@href="#"]', 'Welcome to our store', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['Welcome to our store', null],
            [
                'Goodbye! See you again',
                new Exception('Actual text "Goodbye! See you again" did not match "Welcome to our store"'),
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
