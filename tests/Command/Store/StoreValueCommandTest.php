<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreValueCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreValueCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreValueCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Variable to store element value';
    protected string $group = 'store';

    protected function createCommand(): StoreValueCommand
    {
        return new StoreValueCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with('age', 23);
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('getAttribute')->with('value')->willReturn(23);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'css selector' === $selector->getMechanism()
                    && '.age' === $selector->getValue();
            }))
            ->willReturn($element);
        $this->command->run('css=.age', 'age', $this->values, $this->driver);
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
