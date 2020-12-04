<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Selenium;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverAlert;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWindow;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;
use Tienvx\Bundle\MbtBundle\Selenium\Helper;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Selenium\Helper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class HelperTest extends TestCase
{
    protected RemoteWebDriver $driver;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(RemoteWebDriver::class);
    }

    public function testQuit(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $helper = new Helper($this->driver);
        $helper->quit();
    }

    public function testClick(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::CLICK);
        $command->setTarget('id=add-to-cart');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('click');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'add-to-cart' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testOpen(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::OPEN);
        $command->setTarget('https://demo.sylius.com/en_US/');
        $this->driver->expects($this->once())->method('get')->with('https://demo.sylius.com/en_US/');
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testSetWindowSize(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::SET_WINDOW_SIZE);
        $command->setTarget('1280x800');
        $window = $this->createMock(WebDriverWindow::class);
        $window->expects($this->once())->method('setSize')->with($this->callback(function ($dimention) {
            return $dimention instanceof WebDriverDimension
                && 1280 === $dimention->getWidth()
                && 800 === $dimention->getHeight();
        }));
        $options = $this->createMock(WebDriverOptions::class);
        $options->expects($this->once())->method('window')->willReturn($window);
        $this->driver->expects($this->once())->method('manage')->willReturn($options);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testType(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::TYPE);
        $command->setTarget('name=age');
        $command->setValue('20 years old');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('sendKeys')->with('20 years old');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'age' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testClear(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::CLEAR);
        $command->setTarget('css=.quantity');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('clear');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.quantity' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertAlertPassed(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_ALERT);
        $command->setTarget('Are you sure you want to close this window?');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('Are you sure you want to close this window?');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertAlertFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Alert is not equal to "Are you sure you want to close this window?"');
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_ALERT);
        $command->setTarget('Are you sure you want to close this window?');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('You have no items in cart!');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertTextPassed(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue('Welcome to our store');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Welcome to our store');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertTextFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "xpath=//h4[@href="#"]" does not have text "Welcome to our store"');
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_TEXT);
        $command->setTarget('xpath=//h4[@href="#"]');
        $command->setValue('Welcome to our store');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getText')->willReturn('Goodbye! See you again');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'xpath' === $selector->getMechanism()
                && '//h4[@href="#"]' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertEditablePassed(): void
    {
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isEnabled')->willReturn(true);
        $element->expects($this->once())->method('getAttribute')->with('readonly')->willReturn(null);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }

    public function testAssertEditableFailed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Element "name=username" is not editable');
        $command = new Command();
        $command->setCommand(CommandInterface::ASSERT_EDITABLE);
        $command->setTarget('name=username');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isEnabled')->willReturn(false);
        $element->expects($this->never())->method('getAttribute');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'username' === $selector->getValue();
        }))->willReturn($element);
        $helper = new Helper($this->driver);
        $helper->replay($command);
    }
}
