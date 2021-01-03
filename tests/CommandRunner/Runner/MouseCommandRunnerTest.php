<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteMouse;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\MouseCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class MouseCommandRunnerTest extends RunnerTestCase
{
    public function testAddSelectionByIndex(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::ADD_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('index=123');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('selectByIndex')->with(123);
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    public function testAddSelectionByValue(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::ADD_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('value=en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('selectByValue')->with('en_GB');
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    public function testAddSelectionByLabel(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::ADD_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('label=English (UK)');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('selectByVisibleText')->with('English (UK)');
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    public function testRemoveSelectionByIndex(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::REMOVE_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('index=123');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('deselectByIndex')->with(123);
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    public function testRemoveSelectionByValue(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::REMOVE_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('value=en_GB');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('deselectByValue')->with('en_GB');
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    public function testRemoveSelectionByLabel(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::REMOVE_SELECTION);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('label=English (UK)');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method('deselectByVisibleText')->with('English (UK)');
        $runner = $this->createPartialMock(MouseCommandRunner::class, ['getSelect']);
        $runner->expects($this->once())->method('getSelect')->with($element)->willReturn($select);
        $runner->run($command, $this->driver);
    }

    /**
     * @dataProvider checkDataProvider
     */
    public function testCheck(bool $selected, bool $checked): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CHECK);
        $command->setTarget('id=language');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn($selected);
        $element->expects($this->exactly(+$checked))->method('click');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'language' === $selector->getValue();
        }))->willReturn($element);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    /**
     * @dataProvider uncheckDataProvider
     */
    public function testunCheck(bool $selected, bool $unchecked): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::UNCHECK);
        $command->setTarget('id=language');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn($selected);
        $element->expects($this->exactly(+$unchecked))->method('click');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'language' === $selector->getValue();
        }))->willReturn($element);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testClick(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK);
        $command->setTarget('id=add-to-cart');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('click');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'add-to-cart' === $selector->getValue();
        }))->willReturn($element);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testClickAt(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::CLICK_AT);
        $command->setTarget('id=cart');
        $command->setValue('5,10');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $action = $this->createMock(WebDriverActions::class);
        $action->expects($this->once())->method('moveToElement')->with($element, 5, 10)->willReturnSelf();
        $action->expects($this->once())->method('click')->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testDoubleClick(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::DOUBLE_CLICK);
        $command->setTarget('id=cart');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $action = $this->createMock(WebDriverActions::class);
        $action->expects($this->once())->method('doubleClick')->with($element)->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testDoubleClickAt(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::DOUBLE_CLICK_AT);
        $command->setTarget('id=cart');
        $command->setValue('5,10');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $action = $this->createMock(WebDriverActions::class);
        $action->expects($this->once())->method('moveToElement')->with($element, 5, 10)->willReturnSelf();
        $action->expects($this->once())->method('doubleClick')->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testDragAndDropToObject(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::DRAG_AND_DROP_TO_OBJECT);
        $command->setTarget('id=product');
        $command->setValue('id=cart');
        $source = $this->createMock(WebDriverElement::class);
        $target = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->exactly(2))
            ->method('findElement')
            ->withConsecutive(
                [$this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'id' === $selector->getMechanism()
                        && 'product' === $selector->getValue();
                })],
                [$this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'id' === $selector->getMechanism()
                        && 'cart' === $selector->getValue();
                })],
            )->willReturnOnConsecutiveCalls($source, $target);
        $action = $this->createMock(WebDriverActions::class);
        $action->expects($this->once())->method('dragAndDrop')->with($source, $target)->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseDown(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_DOWN);
        $command->setTarget('id=cart');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseDown')->with($coord);
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseDownAt(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_DOWN_AT);
        $command->setTarget('id=cart');
        $command->setValue('5,10');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseMove')->with($coord, 5, 10)->willReturnSelf();
        $mouse->expects($this->once())->method('mouseDown');
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseMoveAt(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_MOVE_AT);
        $command->setTarget('id=cart');
        $command->setValue('5,10');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseMove')->with($coord, 5, 10);
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseOver(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_OVER);
        $command->setTarget('id=cart');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseMove')->with($coord);
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseUp(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_UP);
        $command->setTarget('id=cart');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseUp')->with($coord);
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testMouseUpAt(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::MOUSE_UP_AT);
        $command->setTarget('id=cart');
        $command->setValue('5,10');
        $coord = $this->createMock(WebDriverCoordinates::class);
        $element = $this->createMock(RemoteWebElement::class);
        $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        $mouse = $this->createMock(RemoteMouse::class);
        $mouse->expects($this->once())->method('mouseMove')->with($coord, 5, 10)->willReturnSelf();
        $mouse->expects($this->once())->method('mouseUp');
        $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testSelect(): void
    {
        $command = new Command();
        $command->setCommand(MouseCommandRunner::SELECT);
        $command->setTarget('partialLinkText=Language');
        $command->setValue('css=option[value=en_US]');
        $select = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($select);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('click');
        $select->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && 'option[value=en_US]' === $selector->getValue();
        }))->willReturn($option);
        $runner = new MouseCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function checkDataProvider(): array
    {
        return [
            [true, false],
            [false, true],
        ];
    }

    public function uncheckDataProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
