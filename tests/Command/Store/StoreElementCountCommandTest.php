<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreElementCountCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreElementCountCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreElementCountCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Variable to store element count';
    protected string $group = 'store';

    protected function createCommand(): StoreElementCountCommand
    {
        return new StoreElementCountCommand();
    }

    public function testRun(): void
    {
        $this->values->expects($this->once())->method('setValue')->with('itemCount', 2);
        $element = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElements')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && '.item' === $selector->getValue();
        }))->willReturn([$element, $element]);
        $this->command->run('css=.item', 'itemCount', $this->values, $this->driver);
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
