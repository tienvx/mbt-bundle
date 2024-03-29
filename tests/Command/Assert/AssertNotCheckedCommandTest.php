<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotCheckedCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotCheckedCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertNotCheckedCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertNotCheckedCommand
    {
        return new AssertNotCheckedCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $isSelected, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->element->expects($this->once())->method('isSelected')->willReturn($isSelected);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'css selector' === $selector->getMechanism()
                    && '.term-and-condition' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('css=.term-and-condition', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [false, null],
            [true, new Exception('Element "css=.term-and-condition" is checked, expected to be unchecked')],
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
            [null, true],
            ['', true],
            ['anything', true],
        ];
    }
}
