<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotVisibleCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotVisibleCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\AbstractWaitCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class WaitForElementNotVisibleCommandTest extends WaitTestCase
{
    protected function createCommand(): WaitForElementNotVisibleCommand
    {
        return new WaitForElementNotVisibleCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $displayed, bool $present, bool $stale): void
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
            $mock = $element
                ->expects($this->once())
                ->method('isDisplayed');
            if ($stale) {
                $mock->willThrowException(new StaleElementReferenceException('Element gone'));
            } else {
                $mock->willReturn($displayed);
            }
        } else {
            $mock->willThrowException(new NoSuchElementException('Element missing'));
        }
        $wait = $this->createMock(WebDriverWait::class);
        $wait
            ->expects($this->once())
            ->method('until')
            ->with($this->callback(function (WebDriverExpectedCondition $condition) use ($displayed, $present, $stale) {
                return (!$displayed || !$present || $stale) === call_user_func($condition->getApply(), $this->driver);
            }));
        $this->driver->expects($this->once())->method('wait')->with(123)->willReturn($wait);
        $this->command->run('id=title', 123, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [false, true, false],
            [true, true, false],
            [false, false, false],
            [false, true, true],
        ];
    }
}
