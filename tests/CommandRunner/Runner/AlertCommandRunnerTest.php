<?php

namespace Tienvx\Bundle\MbtBundle\Tests\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteTargetLocator;
use Facebook\WebDriver\WebDriverAlert;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Command;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Command\Runner\AlertCommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Command\CommandRunner
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Command
 */
class AlertCommandRunnerTest extends RunnerTestCase
{
    protected function createRunner(): CommandRunner
    {
        return new AlertCommandRunner();
    }

    /**
     * @dataProvider acceptAlertCommandProvider
     */
    public function testAcceptAlert(string $acceptAlertCommand): void
    {
        $command = new Command();
        $command->setCommand($acceptAlertCommand);
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('accept');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->color, $this->driver);
    }

    /**
     * @dataProvider dismissAlertCommandProvider
     */
    public function testDismissAlert(string $acceptAlertCommand): void
    {
        $command = new Command();
        $command->setCommand($acceptAlertCommand);
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('dismiss');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function testAnswerPrompt(): void
    {
        $command = new Command();
        $command->setCommand(AlertCommandRunner::ANSWER_PROMPT);
        $command->setTarget('Yes, I agree');
        $alert = $this->createMock(WebDriverAlert::class);
        $alert->expects($this->once())->method('sendKeys')->with('Yes, I agree');
        $alert->expects($this->once())->method('accept');
        $targetLocator = $this->createMock(RemoteTargetLocator::class);
        $targetLocator->expects($this->once())->method('alert')->willReturn($alert);
        $this->driver->expects($this->once())->method('switchTo')->willReturn($targetLocator);
        $this->runner->run($command, $this->color, $this->driver);
    }

    public function acceptAlertCommandProvider(): array
    {
        return [
            [AlertCommandRunner::ACCEPT_ALERT],
            [AlertCommandRunner::ACCEPT_CONFIRMATION],
        ];
    }

    public function dismissAlertCommandProvider(): array
    {
        return [
            [AlertCommandRunner::DISMISS_CONFIRMATION],
            [AlertCommandRunner::DISMISS_PROMPT],
        ];
    }
}
