<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Mouse\DragAndDropToObjectCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\DragAndDropToObjectCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class DragAndDropToObjectCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Source locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = "Target locator e.g. 'id=email' or 'css=.last-name'";
    protected string $group = 'mouse';

    protected function createCommand(): DragAndDropToObjectCommand
    {
        return new DragAndDropToObjectCommand();
    }

    public function testRun(): void
    {
        $source = $this->createMock(WebDriverElement::class);
        $target = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->exactly(2))
            ->method('findElement')
            ->withConsecutive(
                [$this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'id' === $selector->getMechanism()
                        && 'product' === $selector->getValue();
                })],
                [$this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'id' === $selector->getMechanism()
                        && 'cart' === $selector->getValue();
                })],
            )->willReturnOnConsecutiveCalls($source, $target);
        $action = $this->createMock(WebDriverActions::class);
        $action
            ->expects($this->once())
            ->method('dragAndDrop')
            ->with($source, $target)
            ->willReturnSelf();
        $action->expects($this->once())->method('perform');
        $this->driver->expects($this->once())->method('action')->willReturn($action);
        $this->command->run('id=product', 'id=cart', $this->values, $this->driver);
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
            ['css=#selector', true],
        ];
    }
}
