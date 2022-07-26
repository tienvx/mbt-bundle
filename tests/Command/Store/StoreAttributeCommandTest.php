<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Store;

use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Store\StoreAttributeCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\StoreAttributeCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Store\AbstractStoreCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class StoreAttributeCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Attribute locator e.g. 'id=email@readonly' or 'css=.last-name@size'";
    protected string $valueHelper = 'Variable to store attribute value';
    protected string $group = 'store';

    protected function createCommand(): StoreAttributeCommand
    {
        return new StoreAttributeCommand();
    }

    public function testRun(): void
    {
        $this->values
            ->expects($this->once())
            ->method('setValue')
            ->with('readmoreLink', 'http://example.com');
        $this->element
            ->expects($this->once())
            ->method('getAttribute')
            ->with('href')
            ->willReturn('http://example.com');
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'css selector' === $selector->getMechanism()
                    && '.readmore' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->command->run('css=.readmore@href', 'readmoreLink', $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['key@value', false],
            ['css=#selector', false],
            ['css=#selector@attr', true],
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
