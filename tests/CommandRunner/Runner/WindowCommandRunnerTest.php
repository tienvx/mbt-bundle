<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverWindow;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\WindowCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class WindowCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new WindowCommandRunner();
    }

    public function testOpen(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::OPEN);
        $command->setTarget('https://demo.sylius.com/en_US/');
        $this->driver->expects($this->once())->method('get')->with('https://demo.sylius.com/en_US/');
        $this->runner->run($command, $this->driver);
    }

    public function testSetWindowSize(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SET_WINDOW_SIZE);
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
        $this->runner->run($command, $this->driver);
    }

    public function testSelectWindow(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SELECT_WINDOW);
        $command->setTarget('handle=testing');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('window')->with('testing');
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->driver);
    }

    public function testClose(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::CLOSE);
        $this->driver->expects($this->once())->method('close');
        $this->runner->run($command, $this->driver);
    }

    public function testSelectFrameRelativeTop(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SELECT_FRAME);
        $command->setTarget('relative=top');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('defaultContent');
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->driver);
    }

    public function testSelectFrameRelativeParent(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SELECT_FRAME);
        $command->setTarget('relative=parent');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('parent');
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->driver);
    }

    public function testSelectFrameIndex(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SELECT_FRAME);
        $command->setTarget('index=123');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('frame')->with(123);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->driver);
    }

    public function testSelectFrameSelector(): void
    {
        $command = new Command();
        $command->setCommand(WindowCommandRunner::SELECT_FRAME);
        $command->setTarget('linkText=Read More');
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->exactly(2))->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'link text' === $selector->getMechanism()
                && 'Read More' === $selector->getValue();
        }))->willReturn($element);
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('frame')->with($element);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $wait = $this->createMock(WebDriverWait::class);
        $wait->expects($this->once())->method('until')->with($this->callback(function ($condition) {
            return $condition instanceof WebDriverExpectedCondition
                && call_user_func($condition->getApply(), $this->driver);
        }));
        $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        $this->runner->run($command, $this->driver);
    }
}
