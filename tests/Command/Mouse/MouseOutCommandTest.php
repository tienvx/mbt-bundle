<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Exception;
use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\Remote\RemoteMouse;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Mouse\MouseOutCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\MouseOutCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class MouseOutCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'mouse';

    protected function createCommand(): MouseOutCommand
    {
        return new MouseOutCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(object $rect, object $vp, ?int $x, ?int $y, ?Exception $exception): void
    {
        $element = $this->createMock(RemoteWebElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'id' === $selector->getMechanism()
                && 'cart' === $selector->getValue();
        }))->willReturn($element);
        if ($exception) {
            $this->driver->expects($this->never())->method('getMouse');
            $this->expectExceptionObject($exception);
        } else {
            $coord = $this->createMock(WebDriverCoordinates::class);
            $element->expects($this->once())->method('getCoordinates')->willReturn($coord);
            $mouse = $this->createMock(RemoteMouse::class);
            $mouse->expects($this->once())->method('mouseMove')->with($coord, $x, $y);
            $this->driver->expects($this->once())->method('getMouse')->willReturn($mouse);
        }
        $this->driver->expects($this->once())->method('executeScript')->with(
            'return [arguments[0].getBoundingClientRect(), {height: window.innerHeight, width: window.innerWidth}];',
            [$element]
        )->willReturn([$rect, $vp]);
        $this->command->run('id=cart', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [
                'rect' => (object) ['top' => 1, 'height' => 2],
                'vp' => (object) [],
                'x' => null,
                'y' => -2,
                'exception' => null,
            ],
            [
                'rect' => (object) ['top' => 0, 'right' => 2],
                'vp' => (object) ['width' => 3],
                'x' => 2,
                'y' => null,
                'exception' => null,
            ],
            [
                'rect' => (object) ['top' => 0, 'right' => 2, 'bottom' => 1, 'height' => 4],
                'vp' => (object) ['width' => 2, 'height' => 2],
                'x' => null,
                'y' => 3,
                'exception' => null,
            ],
            [
                'rect' => (object) ['top' => 0, 'right' => 2, 'bottom' => 1, 'left' => 1],
                'vp' => (object) ['width' => 2, 'height' => 1],
                'x' => -1,
                'y' => null,
                'exception' => null,
            ],
            [
                'rect' => (object) ['top' => 0, 'right' => 2, 'bottom' => 1, 'left' => 0],
                'vp' => (object) ['width' => 2, 'height' => 1],
                'x' => null,
                'y' => null,
                'exception' => new Exception('Unable to perform mouse out as the element takes up the entire viewport'),
            ],
        ];
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
