<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Alert;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Tienvx\Bundle\MbtBundle\Command\Alert\DismissPromptCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Alert\DismissPromptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Alert\AbstractAlertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class DismissPromptCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = false;
    protected bool $isValueRequired = false;
    protected string $targetHelper = '';
    protected string $valueHelper = '';
    protected string $group = 'alert';

    protected function createCommand(): DismissPromptCommand
    {
        return new DismissPromptCommand();
    }

    public function testRun(): void
    {
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('dismiss');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->command->run(null, null, $this->values, $this->driver);
    }

    public function targetProvider(): array
    {
        return [
            [null, true],
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
