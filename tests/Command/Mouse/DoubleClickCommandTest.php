<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Mouse\DoubleClickCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\DoubleClickCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class DoubleClickCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'mouse';

    protected function createCommand(): DoubleClickCommand
    {
        return new DoubleClickCommand();
    }

    public function testRun(): void
    {
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($this->element);
        $action = $this->createMock(WebDriverActions::class);
        $action->expects($this->once())->method('doubleClick')->with($this->element)->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $this->command->run('id=cart', null, $this->values, $this->driver);
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
            [null, true],
            ['', true],
            ['anything', true],
        ];
    }
}
