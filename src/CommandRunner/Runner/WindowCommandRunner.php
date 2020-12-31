<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class WindowCommandRunner extends CommandRunner
{
    public const OPEN = 'open';
    public const SET_WINDOW_SIZE = 'setWindowSize';

    public const ALL_COMMANDS = [
        self::OPEN,
    ];

    public function getActions(): array
    {
        return [
            'Open' => self::OPEN,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::OPEN:
                $driver->get($command->getTarget());
                break;
            case self::SET_WINDOW_SIZE:
                $driver->manage()->window()->setSize($this->getDimension($command->getTarget()));
                break;
        }
    }
}
