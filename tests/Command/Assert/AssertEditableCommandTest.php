<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\WebDriverBy;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertEditableCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertEditableCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertEditableCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Locator e.g. 'id=email' or 'css=.last-name'";
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertEditableCommand
    {
        return new AssertEditableCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(bool $enabled, bool $readonly, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $this->driver
            ->expects($this->once())
            ->method('findElement')
            ->with($this->callback(function ($selector) {
                return $selector instanceof WebDriverBy
                    && 'name' === $selector->getMechanism()
                    && 'username' === $selector->getValue();
            }))
            ->willReturn($this->element);
        $this->driver
            ->expects($this->once())
            ->method('executeScript')
            ->with(
                'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
                [$this->element]
            )
            ->willReturn((object) ['enabled' => $enabled, 'readonly' => $readonly]);
        $this->command->run('name=username', null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        $exception = new Exception('Element "name=username" is not editable');

        return [
            [true, false, null],
            [true, true, $exception],
            [false, true, $exception],
            [false, false, $exception],
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
