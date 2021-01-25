<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Closure;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\WaitCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class WaitCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new WaitCommandRunner();
    }

    public function testWaitForElementEditable(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_EDITABLE);
        $command->setTarget('id=name');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'name' === $selector->getValue();
        }))->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return is_callable($condition)
                && $condition instanceof Closure
                && $condition();
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => true, 'readonly' => false]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testWaitForElementNotEditable(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_NOT_EDITABLE);
        $command->setTarget('id=avatar');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'avatar' === $selector->getValue();
        }))->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return is_callable($condition)
                && $condition instanceof Closure
                && $condition();
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with('return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };', [$element])
            ->willReturn((object) ['enabled' => false, 'readonly' => true]);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testWaitForElementPresent(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_PRESENT);
        $command->setTarget('id=title');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'title' === $selector->getValue();
        }))->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return $condition instanceof WebDriverExpectedCondition
                && call_user_func($condition->getApply(), $this->driver);
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testNoElementsPresent(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_NOT_PRESENT);
        $command->setTarget('css=button');
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && 'button' === $selector->getValue();
        }))->willReturn([]);
        $this->driver->expects($this->never())->method('wait');
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testWaitForElementNotPresent(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_NOT_PRESENT);
        $command->setTarget('css=button');
        $element = $this->createMock(WebDriverElement::class);
        $element
            ->expects($this->once())
            ->method('isEnabled')
            ->willThrowException(new StaleElementReferenceException('Element gone'));
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && 'button' === $selector->getValue();
        }))->willReturn([$element]);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return $condition instanceof WebDriverExpectedCondition
                && call_user_func($condition->getApply(), $this->driver);
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testWaitForElementVisible(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_VISIBLE);
        $command->setTarget('id=title');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isDisplayed')->willReturn(true);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'title' === $selector->getValue();
        }))->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return $condition instanceof WebDriverExpectedCondition
                && call_user_func($condition->getApply(), $this->driver);
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testWaitForElementNotVisible(): void
    {
        $command = new Command();
        $command->setCommand(WaitCommandRunner::WAIT_FOR_ELEMENT_NOT_VISIBLE);
        $command->setTarget('id=title');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isDisplayed')->willReturn(false);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'title' === $selector->getValue();
        }))->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return $condition instanceof WebDriverExpectedCondition
                && call_user_func($condition->getApply(), $this->driver);
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [WaitCommandRunner::WAIT_FOR_ELEMENT_VISIBLE, null, false],
            [WaitCommandRunner::WAIT_FOR_ELEMENT_VISIBLE, 'anything', false],
            [WaitCommandRunner::WAIT_FOR_ELEMENT_VISIBLE, 'xpath=//path/to/element', true],
        ];
    }
}
