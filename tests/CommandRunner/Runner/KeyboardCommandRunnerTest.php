<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\Runner\KeyboardCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class KeyboardCommandRunnerTest extends RunnerTestCase
{
    public function testType(): void
    {
        $command = new Command();
        $command->setCommand(KeyboardCommandRunner::TYPE);
        $command->setTarget('name=age');
        $command->setValue('20 years old');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('click')->willReturnSelf();
        $element->expects($this->once())->method('clear')->willReturnSelf();
        $element->expects($this->once())->method('sendKeys')->with(['20 years old']);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'name' === $selector->getMechanism()
                && 'age' === $selector->getValue();
        }))->willReturn($element);
        $runner = new KeyboardCommandRunner();
        $runner->run($command, $this->driver);
    }

    public function testSendKeys(): void
    {
        $command = new Command();
        $command->setCommand(KeyboardCommandRunner::SEND_KEYS);
        $command->setTarget('css=.quantity');
        $command->setValue('123');
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('click')->willReturnSelf();
        $element->expects($this->once())->method('sendKeys')->with(['123']);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.quantity' === $selector->getValue();
        }))->willReturn($element);
        $runner = new KeyboardCommandRunner();
        $runner->run($command, $this->driver);
    }
}
