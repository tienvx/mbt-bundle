<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Mouse\ClickAtCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\ClickAtCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMousePointCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class ClickAtCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Point: x-coordinate,y-coordinate';
    protected string $group = 'mouse';

    protected function createCommand(): ClickAtCommand
    {
        return new ClickAtCommand();
    }

    public function testRun(): void
    {
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
        $this->command->run('id=cart', '5,10', $this->values, $this->driver);
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
            ['anything', false],
            ['123', false],
            ['123,', false],
            [',123', false],
            ['123,456', true],
        ];
    }
}
