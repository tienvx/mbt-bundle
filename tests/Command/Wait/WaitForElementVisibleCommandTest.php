<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementVisibleCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementVisibleCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\AbstractWaitCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class WaitForElementVisibleCommandTest extends WaitTestCase
{
    protected function createCommand(): WaitForElementVisibleCommand
    {
        return new WaitForElementVisibleCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $displayed, bool $stale): void
    {
        $element = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'id' === $selector->getMechanism()
                    && 'title' === $selector->getValue();
            }))
            ->willReturn($element);
        $mock = $element
            ->expects($this->once())
            ->method('isDisplayed');
        if ($stale) {
            $mock->willThrowException(new StaleElementReferenceException('Element gone'));
        } else {
            $mock->willReturn($displayed);
        }
        $wait = $this->createMock(WebDriverWait::class);
        $wait
            ->expects($this->once())
            ->method('until')
            ->with($this->callback(function (WebDriverExpectedCondition $condition) use ($displayed, $stale) {
                return ($displayed && !$stale) === !empty(call_user_func($condition->getApply(), $this->driver));
            }));
        $this->driver->expects($this->once())->method('wait')->with(123)->willReturn($wait);
        $this->command->run('id=title', 123, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [true, false],
            [false, false],
            [false, true],
        ];
    }
}
