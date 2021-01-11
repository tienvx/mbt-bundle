<?php

namespace Tienvx\Bundle\MbtBundle\Command\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class ScriptCommandRunner extends CommandRunner
{
    public const RUN_SCRIPT = 'runScript';
    public const EXECUTE_SCRIPT = 'executeScript';
    public const EXECUTE_ASYNC_SCRIPT = 'executeAsyncScript';

    public function getAllCommands(): array
    {
        return [
            'Run Script' => self::RUN_SCRIPT,
            'Execute Script' => self::EXECUTE_SCRIPT,
            'Execute Async Script' => self::EXECUTE_ASYNC_SCRIPT,
        ];
    }

    public function getCommandsRequireTarget(): array
    {
        return array_values($this->getAllCommands());
    }

    public function getCommandsRequireValue(): array
    {
        return [];
    }

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void
    {
        switch ($command->getCommand()) {
            case self::RUN_SCRIPT:
                $driver->executeScript($command->getTarget(), $color->getValues());
                break;
            case self::EXECUTE_SCRIPT:
                $value = $driver->executeScript($command->getTarget(), $color->getValues());
                if ($command->getValue()) {
                    $color->setValue($command->getValue(), $value);
                }
                break;
            case self::EXECUTE_ASYNC_SCRIPT:
                $value = $driver->executeAsyncScript($command->getTarget(), $color->getValues());
                if ($command->getValue()) {
                    $color->setValue($command->getValue(), $value);
                }
                break;
        }
    }
}
