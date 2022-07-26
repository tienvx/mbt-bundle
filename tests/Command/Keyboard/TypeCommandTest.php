<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Keyboard;

use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Keyboard\TypeCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Keyboard\TypeCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Keyboard\AbstractKeyboardCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class TypeCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Text to be filled into target';
    protected string $group = 'keyboard';

    protected function createCommand(): TypeCommand
    {
        return new TypeCommand();
    }

    public function testRun(): void
    {
        $this->element->expects($this->once())->method('click')->willReturnSelf();
        $this->element->expects($this->once())->method('clear')->willReturnSelf();
        $this->element->expects($this->once())->method('sendKeys')->with('20 years old');
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'name' === $selector->getMechanism()
                    && 'age' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('name=age', '20 years old', $this->values, $this->driver);
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
