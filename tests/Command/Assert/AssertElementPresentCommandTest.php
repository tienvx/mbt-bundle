<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertElementPresentCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertElementPresentCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertElementPresentCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertElementPresentCommand
    {
        return new AssertElementPresentCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(int $count, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $element = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->once())
            ->method('findElements')
            ->with(
                $this->callback(function ($selector) {
                    return $selector instanceof WebDriverBy
                        && 'css selector' === $selector->getMechanism()
                        && '.cart' === $selector->getValue();
                })
            )
            ->willReturn(array_fill(0, $count, $element));
        $this->command->run('css=.cart', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        $exception = new Exception('Expected element "css=.cart" was not found in page');

        return [
            [0, $exception],
            [1, null],
            [2, null],
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
