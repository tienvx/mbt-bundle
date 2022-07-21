<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertSelectedValueCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertSelectedValueCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertSelectedValueCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Expected selected value';
    protected string $group = 'assert';

    protected function createCommand(): AssertSelectedValueCommand
    {
        return new AssertSelectedValueCommand();
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
        $command = $this->createPartialMock(AssertSelectedValueCommand::class, ['getSelect']);
        $command->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $command->run('partialLinkText=Language', 'en_GB', $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['en_GB', null],
            ['en_US', new Exception('Actual value "en_US" did not match "en_GB"')],
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
