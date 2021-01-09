<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

class CommandRunnerManager implements CommandRunnerManagerInterface
{
    protected iterable $runners;

    public function __construct(iterable $runners)
    {
        $this->runners = $runners;
    }

    public function getAllCommands(): array
    {
        return $this->getCommands();
    }

    public function getCommandsRequireTarget(): array
    {
        return $this->getCommands('getCommandsRequireTarget');
    }

    public function getCommandsRequireValue(): array
    {
        return $this->getCommands('getCommandsRequireValue');
    }

    public function run(CommandInterface $command, RemoteWebDriver $driver): void
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface && $runner->supports($command)) {
                $runner->run($command, $driver);
                break;
            }
        }
    }

    protected function getCommands(string $method = 'getAllCommands'): array
    {
        $actions = [];
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface) {
                $actions = array_merge($actions, call_user_func([$runner, $method]));
            }
        }

        return $actions;
    }
}
