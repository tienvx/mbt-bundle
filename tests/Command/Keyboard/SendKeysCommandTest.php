<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Keyboard;

use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Keyboard\SendKeysCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Keyboard\SendKeysCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Keyboard\AbstractKeyboardCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class SendKeysCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Text to be appended into target';
    protected string $group = 'keyboard';

    protected function createCommand(): SendKeysCommand
    {
        return new SendKeysCommand();
    }

    public function testRun(): void
    {
        $this->element->expects($this->once())->method('click')->willReturnSelf();
        $this->element->expects($this->once())->method('sendKeys')->with('123');
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.quantity' === $selector->getValue();
        }))->willReturn($this->element);
        $this->command->run('css=.quantity', '123', $this->values, $this->driver);
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
            ['', false],
            ['anything', true],
        ];
    }
}
