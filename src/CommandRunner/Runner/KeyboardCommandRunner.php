<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\CommandRunner\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class KeyboardCommandRunner extends CommandRunner
{
    public const TYPE = 'type';
    public const CLEAR = 'clear';

    public const ALL_COMMANDS = [
        self::TYPE,
        self::CLEAR,
    ];

    public function getActions(): array
    {
        return [
            'Type' => self::TYPE,
            'Clear' => self::CLEAR,
        ];
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::TYPE:
                $webDriverBy = $this->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->sendKeys($command->getValue());
                break;
            case self::CLEAR:
                $webDriverBy = $this->getSelector($command->getTarget());
                $driver->findElement($webDriverBy)->clear();
                break;
        }
    }
}
