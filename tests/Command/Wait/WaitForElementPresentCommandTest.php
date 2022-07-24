<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementPresentCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementPresentCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\AbstractWaitCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class WaitForElementPresentCommandTest extends WaitTestCase
{
    protected function createCommand(): WaitForElementPresentCommand
    {
        return new WaitForElementPresentCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $present): void
    {
        $element = $this->createMock(WebDriverElement::class);
        $mock = $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'id' === $selector->getMechanism()
                    && 'title' === $selector->getValue();
            }));
        if ($present) {
            $mock->willReturn($element);
        } else {
            $mock->willThrowException(new NoSuchElementException('Element missing'));
        }
        $wait = $this->createMock(WebDriverWait::class);
        $wait
            ->expects($this->once())
            ->method('until')
            ->with($this->callback(function (WebDriverExpectedCondition $condition) use ($present) {
                return $present === !empty(call_user_func($condition->getApply(), $this->driver));
            }));
        $this->driver->expects($this->once())->method('wait')->with(123)->willReturn($wait);
        $this->command->run('id=title', 123, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [false],
            [true],
        ];
    }
}
