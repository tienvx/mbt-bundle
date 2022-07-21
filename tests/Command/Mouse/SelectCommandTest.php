<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Mouse\SelectCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\SelectCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class SelectCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Select locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = "Option locator e.g. 'id=email' or 'css=.last-name'";
    protected string $group = 'mouse';

    protected function createCommand(): SelectCommand
    {
        return new SelectCommand();
    }

    public function testRun(): void
    {
        $select = $this->createMock(WebDriverElement::class);
        $this->driver->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'partial link text' === $selector->getMechanism()
                && 'Language' === $selector->getValue();
        }))->willReturn($select);
        $option = $this->createMock(WebDriverElement::class);
        $option->expects($this->once())->method('click');
        $select->expects($this->once())->method('findElement')->with($this->callback(function ($selector) {
            return $selector instanceof WebDriverBy
                && 'css selector' === $selector->getMechanism()
                && 'option[value=en_US]' === $selector->getValue();
        }))->willReturn($option);
        $this->command->run('partialLinkText=Language', 'css=option[value=en_US]', $this->values, $this->driver);
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
