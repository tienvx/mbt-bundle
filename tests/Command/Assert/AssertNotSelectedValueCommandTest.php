<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedValueCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotSelectedValueCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertNotSelectedValueCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Unexpected selected value';
    protected string $group = 'assert';

    protected function createCommand(): AssertNotSelectedValueCommand
    {
        return new AssertNotSelectedValueCommand();
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
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'partial link text' === $selector->getMechanism()
                    && 'Language' === $selector->getValue();
            }))
            ->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getAttribute')->with('value')->willReturn($actual);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $command = $this->createPartialMock(AssertNotSelectedValueCommand::class, ['getSelect']);
        $command->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $command->run('partialLinkText=Language', 'en_GB', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['en_US', null],
            ['en_GB', new Exception('Actual value "en_GB" did match "en_GB"')],
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
