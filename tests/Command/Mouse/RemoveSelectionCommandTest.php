<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Mouse;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Command\Mouse\RemoveSelectionCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\RemoveSelectionCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Mouse\AbstractMouseSelectionCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class RemoveSelectionCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = true;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = 'Deselect by e.g. index=12, value=something or label=something else';
    protected string $group = 'mouse';

    protected function createCommand(): RemoveSelectionCommand
    {
        return new RemoveSelectionCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $value, string $method, string|int $with): void
    {
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'partial link text' === $selector->getMechanism()
                    && 'Language' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $select = $this->createMock(WebDriverSelect::class);
        $select->expects($this->once())->method($method)->with($with);
        $command = $this->createPartialMock(RemoveSelectionCommand::class, ['getSelect']);
        $command->expects($this->once())->method('getSelect')->with($this->element)->willReturn($select);
        $command->run('partialLinkText=Language', $value, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['index=123', 'deselectByIndex', 123],
            ['value=en_GB', 'deselectByValue', 'en_GB'],
            ['label=English (UK)', 'deselectByVisibleText', 'English (UK)'],
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
            [null, false],
            ['', false],
            ['anything', false],
            ['=', false],
            ['key=value', false],
            ['index=123', true],
            ['value=abc123', true],
            ['label=Text', true],
        ];
    }
}
