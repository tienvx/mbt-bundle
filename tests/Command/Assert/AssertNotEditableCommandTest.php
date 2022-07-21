<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotEditableCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertNotEditableCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertNotEditableCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertNotEditableCommand
    {
        return new AssertNotEditableCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $enabled, bool $readonly, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $element = $this->createMock(WebDriverElement::class);
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'name' === $selector->getMechanism()
                    && 'username' === $selector->getValue();
            }))
            ->willReturn($element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with(
                'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
                [$element]
            )
            ->willReturn((object) ['enabled' => $enabled, 'readonly' => $readonly]);
        $this->command->run('name=username', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        $exception = new Exception('Element "name=username" is editable');

        return [
            [true, false, $exception],
            [true, true, null],
            [false, true, null],
            [false, false, null],
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
