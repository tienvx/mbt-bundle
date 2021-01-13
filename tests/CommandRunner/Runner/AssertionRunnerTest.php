<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AssertionRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class AssertionRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new AssertionRunner();
    }

    public function testAssertPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT);
        $command->setTarget('var name');
        $command->setValue('var value');
        $this->color->expects($this->once())->method('getValue')->with('var name')->willReturn('var value');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertFailed(): void
    {
        $expected = 'var value';
        $actual = 'var value 1';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Actual value "%s" did not match "%s"', $actual, $expected));
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT);
        $command->setTarget('var name');
        $command->setValue($expected);
        $this->color->expects($this->once())->method('getValue')->with('var name')->willReturn($actual);
        $this->runner->run($command, $this->color, $this->driver);
    }

    /**
     * @dataProvider alertCommandProvider
     */
    public function testAssertAlertPassed(string $alertCommand): void
    {
        $command = new Command();
        $command->setCommand($alertCommand);
        $command->setTarget('Are you sure you want to close this window?');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('Are you sure you want to close this window?');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $this->runner->run($command, $this->color, $this->driver);
    }

    /**
     * @dataProvider alertCommandProvider
     */
    public function testAssertAlertFailed(string $alertCommand, string $type): void
    {
        $expected = 'Are you sure you want to close this window?';
        $actual = 'Dont close this window!';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Actual %s text "%s" did not match "%s"', $type, $actual, $expected));
        $command = new Command();
        $command->setCommand($alertCommand);
        $command->setTarget($expected);
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn($actual);
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertTitlePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_TITLE);
        $command->setTarget('Welcome');
        $this->driver->expects($this->exactly(2))->method('getTitle')->willReturn('Welcome');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertTitleFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual title "Goodbye" did not match "Welcome"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_TITLE);
        $command->setTarget('Welcome');
        $this->driver->expects($this->exactly(2))->method('getTitle')->willReturn('Goodbye');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertTextPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue('Welcome to our store');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Welcome to our store');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertTextFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual text "Goodbye! See you again" did not match "Welcome to our store"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue('Welcome to our store');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Goodbye! See you again');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotTextFailed(): void
    {
        $expected = 'Welcome to our store';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Actual text "%s" did match "%s"', $expected, $expected));
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue($expected);
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn($expected);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotTextPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue('Welcome to our store');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Goodbye! See you again');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertValuePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_VALUE);
        $command->setTarget('css=.quality');
        $command->setValue('14');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getAttribute')->with('value')->willReturn('14');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.quality' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertValueFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual value "15" did not match "14"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_VALUE);
        $command->setTarget('css=.quality');
        $command->setValue('14');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getAttribute')->with('value')->willReturn('15');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.quality' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertEditablePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => true, 'readonly' => false]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertEditableFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "name=username" is not editable');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => false, 'readonly' => true]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotEditableFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "name=username" is editable');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => true, 'readonly' => false]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotEditablePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => false, 'readonly' => true]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertElementPresentPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ELEMENT_PRESENT);
        $command->setTarget('css=.cart');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.cart' === $selector->getValue();
        }))->willReturn([$element]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertElementPresentFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expected element "css=.cart" was not found in page');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ELEMENT_PRESENT);
        $command->setTarget('css=.cart');
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.cart' === $selector->getValue();
        }))->willReturn([]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertElementNotPresentFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unexpected element "css=.cart" was found in page');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ELEMENT_NOT_PRESENT);
        $command->setTarget('css=.cart');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.cart' === $selector->getValue();
        }))->willReturn([$element]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertElementNotPresentPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ELEMENT_NOT_PRESENT);
        $command->setTarget('css=.cart');
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.cart' === $selector->getValue();
        }))->willReturn([]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertCheckedPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_CHECKED);
        $command->setTarget('css=.term-and-condition');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn(true);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.term-and-condition' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertCheckedFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "css=.term-and-condition" is not checked, expected to be checked');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_CHECKED);
        $command->setTarget('css=.term-and-condition');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn(false);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.term-and-condition' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotCheckedFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "css=.term-and-condition" is checked, expected to be unchecked');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_CHECKED);
        $command->setTarget('css=.term-and-condition');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn(true);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.term-and-condition' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotCheckedPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_CHECKED);
        $command->setTarget('css=.term-and-condition');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn(false);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.term-and-condition' === $selector->getValue();
        }))->willReturn($element);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAssertSelectedValuePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_SELECTED_VALUE);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getAttribute')->with('value')->willReturn('en_GB');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertSelectedValueFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual value "en_US" did not match "en_GB"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_SELECTED_VALUE);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getAttribute')->with('value')->willReturn('en_US');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotSelectedValueFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual value "en_GB" did match "en_GB"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_SELECTED_VALUE);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getAttribute')->with('value')->willReturn('en_GB');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotSelectedValuePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_SELECTED_VALUE);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getAttribute')->with('value')->willReturn('en_US');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertSelectedLabelPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_SELECTED_LABEL);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('United Kingdom');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getText')->willReturn('United Kingdom');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertSelectedLabelFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual label "United States" did not match "United Kingdom"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_SELECTED_LABEL);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('United Kingdom');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getText')->willReturn('United States');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotSelectedLabelFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Actual label "United Kingdom" did match "United Kingdom"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_SELECTED_LABEL);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('United Kingdom');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getText')->willReturn('United Kingdom');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function testAssertNotSelectedLabelPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_NOT_SELECTED_LABEL);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('United Kingdom');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('getText')->willReturn('United States');
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('getFirstSelectedOption')->willReturn($option);
        $runner = $this->createPartialMock(AssertionRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->color, $this->driver);
    }

    public function alertCommandProvider(): array
    {
        return [
            [AssertionRunner::ASSERT_ALERT, 'alert'],
            [AssertionRunner::ASSERT_CONFIRMATION, 'confirm'],
            [AssertionRunner::ASSERT_PROMPT, 'prompt'],
        ];
    }
}
