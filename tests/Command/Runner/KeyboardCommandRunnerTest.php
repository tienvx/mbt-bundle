<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Runner;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\KeyboardCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Command
 */
class KeyboardCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new KeyboardCommandRunner();
    }

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
        $this->runner->run($command, $this->color, $this->driver);
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
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [KeyboardCommandRunner::SEND_KEYS, null, false],
            [KeyboardCommandRunner::SEND_KEYS, 'anything', false],
            [KeyboardCommandRunner::SEND_KEYS, 'xpath=//path/to/element', true],
        ];
    }
}
