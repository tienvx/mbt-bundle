<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedLabelCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedLabelCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertNotSelectedLabelCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Unexpected selected label';
    protected string $group = 'assert';

    protected function createCommand(): AssertNotSelectedLabelCommand
    {
        return new AssertNotSelectedLabelCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $actual, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'partial link text' === $selector->getMechanism()
                    && 'Language' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getText')->willReturn($actual);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $command = $this->createPartialMock(AssertNotSelectedLabelCommand::class, ['getSelect']);
        $command->expects($this->once())->method('getSelect')->with($this->element)->willReturn($select);
        $command->run('partialLinkText=Language', 'United Kingdom', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['United States', null],
            ['United Kingdom', new Exception('Actual label "United Kingdom" did match "United Kingdom"')],
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
