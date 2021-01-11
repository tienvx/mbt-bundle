<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class AlertCommandRunner extends CommandRunner
{
    public const ACCEPT_ALERT = 'acceptAlert';
    public const ACCEPT_CONFIRMATION = 'acceptConfirmation';
    public const ANSWER_PROMPT = 'answerPrompt';
    public const DISMISS_CONFIRMATION = 'dismissConfirmation';
    public const DISMISS_PROMPT = 'dismissPrompt';

    public function getAllCommands(): array
    {
        return [
            'Accept Alert' => self::ACCEPT_ALERT,
            'Accept Confirmation' => self::ACCEPT_CONFIRMATION,
            'Answer Prompt' => self::ANSWER_PROMPT,
            'Dismiss Confirmation' => self::DISMISS_CONFIRMATION,
            'Dismiss Prompt' => self::DISMISS_PROMPT,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return [
            self::ANSWER_PROMPT,
        ];
    }

    public function getCommandsRequireValue(): array
    {
        return [];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::ACCEPT_ALERT:
            case self::ACCEPT_CONFIRMATION:
                $driver->switchTo()->alert()->accept();
                break;
            case self::ANSWER_PROMPT:
                $alert = $driver->switchTo()->alert();
                if ($command->getTarget()) {
                    $alert->sendKeys($command->getTarget());
                }
                $alert->accept();
                break;
            case self::DISMISS_CONFIRMATION:
            case self::DISMISS_PROMPT:
                $driver->switchTo()->alert()->dismiss();
                break;
        }
    }
}
