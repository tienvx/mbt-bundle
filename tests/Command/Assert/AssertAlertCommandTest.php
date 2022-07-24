<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Assert;

use Exception;
use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Tienvx\Bundle\MbtBundle\Command\Assert\AssertAlertCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AssertAlertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Assert\AbstractAssertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AssertAlertCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = 'Expected value';
    protected string $valueHelper = '';
    protected string $group = 'assert';

    protected function createCommand(): AssertAlertCommand
    {
        return new AssertAlertCommand();
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun(string $target, ?Exception $exception): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('getText')->willReturn('expected alert');
        $locator = $this->createMock(RemoteTargetLocator::class);
        $locator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($locator);
        $this->command->run($target, null, $this->values, $this->driver);
    }

    public function runProvider(): array
    {
        return [
            ['expected alert', null],
            ['unexpected alert', new Exception('Actual alert text "expected alert" did not match "unexpected alert"')],
        ];
    }

    public function targetProvider(): array
    {
        return [
            [null, false],
            ['', true],
            ['anything', true],
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
