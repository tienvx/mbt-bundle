<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\AssertionRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class AssertionRunnerTest extends RunnerTestCase
{
    public function testAssertAlertPassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ALERT);
        $command->setTarget('Are you sure you want to close this window?');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('Are you sure you want to close this window?');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
    }

    public function testAssertAlertFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Alert is not equal to "Are you sure you want to close this window?"');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_ALERT);
        $command->setTarget('Are you sure you want to close this window?');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('You have no items in cart!');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
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
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
    }

    public function testAssertTextFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "xpath=//h4[@href="#"]" does not have text "Welcome to our store"');
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
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
    }

    public function testAssertEditablePassed(): void
    {
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isEnabled')->willReturn(true);
        $element->expects($this->once())->method('getAttribute')->with('readonly')->willReturn(null);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
    }

    public function testAssertEditableFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "name=username" is not editable');
        $command = new Command();
        $command->setCommand(AssertionRunner::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isEnabled')->willReturn(false);
        $element->expects($this->never())->method('getAttribute');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $runner = new AssertionRunner();
        $runner->run($command, $this->driver);
    }
}
