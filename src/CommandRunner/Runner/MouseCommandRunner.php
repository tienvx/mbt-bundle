<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class MouseCommandRunner extends CommandRunner
{
    public const CLICK = 'click';

    public const ALL_COMMANDS = [
        self::CLICK,
    ];

    public function getActions(): array
    {
        return [
            'Click' => self::CLICK,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::CLICK:
                $webDriverBy = $this->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->click();
                break;
        }
    }
}
