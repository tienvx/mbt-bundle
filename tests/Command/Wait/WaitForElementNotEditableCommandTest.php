<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Closure;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotEditableCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotEditableCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\AbstractWaitCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class WaitForElementNotEditableCommandTest extends WaitTestCase
{
    protected function createCommand(): WaitForElementNotEditableCommand
    {
        return new WaitForElementNotEditableCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $enabled, bool $readonly, bool $editable): void
    {
        $element = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'id' === $selector->getMechanism()
                    && 'name' === $selector->getValue();
            }))
            ->willReturn($element);
        $wait = $this->createMock(WebDriverWait::class);
        $wait
            ->expects($this->once())
            ->method('until')
            ->with($this->callback(fn ($condition) => is_callable($condition)
                    && $condition instanceof Closure
                    && !$editable === $condition()), 'Timed out waiting for element to not be editable');
        $this->driver->expects($this->once())->method('wait')->with(123)->willReturn($wait);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with(
                'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
                [$element]
            )
            ->willReturn((object) ['enabled' => $enabled, 'readonly' => $readonly]);
        $this->command->run('id=name', 123, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [true, false, true],
            [true, true, false],
            [false, true, false],
            [false, false, false],
        ];
    }
}
