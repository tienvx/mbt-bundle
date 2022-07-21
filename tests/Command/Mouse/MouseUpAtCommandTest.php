<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\Remote\RemoteMouse;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseUpAtCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\MouseUpAtCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMousePointCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class MouseUpAtCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Point: x-coordinate,y-coordinate';
    protected string $group = 'mouse';

    protected function createCommand(): MouseUpAtCommand
    {
        return new MouseUpAtCommand();
    }

    public function testRun(): void
    {
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
