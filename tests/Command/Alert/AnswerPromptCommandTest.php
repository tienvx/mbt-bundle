<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command\Alert;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Tienvx\Bundle\MbtBundle\Command\Alert\AnswerPromptCommand;
use Tienvx\Bundle\MbtBundle\Tests\Command\CommandTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Alert\AnswerPromptCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\Alert\AbstractAlertCommand
 * @covers \Tienvx\Bundle\MbtBundle\Command\AbstractCommand
 */
class AnswerPromptCommandTest extends CommandTestCase
{
    protected bool $isTargetRequired = true;
    protected bool $isValueRequired = false;
    protected string $targetHelper = "Propt's answer";
    protected string $valueHelper = '';
    protected string $group = 'alert';

    protected function createCommand(): AnswerPromptCommand
    {
        return new AnswerPromptCommand();
    }

    public function testRun(): void
    {
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('sendKeys')->with('Yes, I agree');
        $alert->expects($this->once())->method('accept');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->command->run('Yes, I agree', null, $this->values, $this->driver);
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
