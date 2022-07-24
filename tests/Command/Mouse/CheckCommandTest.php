<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Mouse\CheckCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\CheckCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class CheckCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'mouse';

    protected function createCommand(): CheckCommand
    {
        return new CheckCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $selected, bool $checked): void
    {
        $element = $this->createMock(WebDriverElement::class);
        $element->expects($this->once())->method('isSelected')->willReturn($selected);
        $element->expects($this->exactly(+$checked))->method('click');
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'id' === $selector->getMechanism()
                    && 'language' === $selector->getValue();
            }))
            ->willReturn($element);
        $this->command->run('id=language', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [true, false],
            [false, true],
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
