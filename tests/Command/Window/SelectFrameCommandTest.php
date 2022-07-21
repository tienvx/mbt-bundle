<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Window;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Window\SelectFrameCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\SelectFrameCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Window\AbstractWindowCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class SelectFrameCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Frame locator e.g. relative=top , relative=parent , index=123 or element locator';
    protected string $valueHelper = '';
    protected string $group = 'window';

    protected function createCommand(): SelectFrameCommand
    {
        return new SelectFrameCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $target, string $method, ?array $params): void
    {
        if (is_null($params)) {
            $element = $this->createMock(WebDriverElement::class);
            $this->driver
                ->expects($this->exactly(2))
                ->method('findElement')
                ->with($this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'link text' === $selector->getMechanism()
                        && 'Read More' === $selector->getValue();
                }))
                ->willReturn($element);
            $params = [$element];
            $wait = $this->createMock(WebDriverWait::class);
            $wait
                ->expects($this->once())
                ->method('until')
                ->with($this->callback(function ($condition) {
                    return $condition instanceof WebDriverExpectedCondition
                        && call_user_func($condition->getApply(), $this->driver);
                }));
            $this->driver->expects($this->once())->method('wait')->willReturn($wait);
        }
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method($method)->with(...$params);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->command->run($target, null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['relative=top', 'defaultContent', []],
            ['relative=parent', 'parent', []],
            ['index=123', 'frame', [123]],
            ['linkText=Read More', 'frame', null],
        ];
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', false],
            ['anything', false],
            ['relative=top', true],
            ['relative=parent', true],
            ['index=123', true],
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
