<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Wait;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotPresentCommand;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\WaitForElementNotPresentCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Wait\AbstractWaitCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class WaitForElementNotPresentCommandTest extends WaitTestCase
{
    protected function createCommand(): WaitForElementNotPresentCommand
    {
        return new WaitForElementNotPresentCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(int $elementsCount, bool $stale): void
    {
        $elements = array_fill(0, $elementsCount, $this->element);
        $this->driver
            ->expects($this->once())
            ->method('findElements')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'css selector' === $selector->getMechanism()
                    && 'button' === $selector->getValue();
            }))
            ->willReturn($elements);
        if ($elementsCount > 0) {
            $mock = $elements[0]
                ->expects($this->once())
                ->method('isEnabled');
            if ($stale) {
                $mock->willThrowException(new StaleElementReferenceException('Element gone'));
            }
            $wait = $this->createMock(WebDriverWait::class);
            $wait
                ->expects($this->once())
                ->method('until')
                ->with(
                    $this->callback(function (WebDriverExpectedCondition $condition) use ($stale) {
                        return $stale === call_user_func($condition->getApply(), $this->driver);
                    })
                );
            $this->driver->expects($this->once())->method('wait')->with(123)->willReturn($wait);
        } else {
            $this->driver->expects($this->never())->method('wait');
        }
        $this->command->run('css=button', 123, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            [0, false],
            [1, false],
            [2, false],
            [1, true],
            [2, true],
        ];
    }
}
