<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

class CommandRunnerManager implements CommandRunnerManagerInterface
{
    protected iterable $runners;
    protected CommandPreprocessorInterface $commandPreprocessor;

    public function __construct(iterable $runners, CommandPreprocessorInterface $commandPreprocessor)
    {
        $this->runners = $runners;
        $this->commandPreprocessor = $commandPreprocessor;
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

    public function validateTarget(CommandInterface $command): bool
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface && $runner->supports($command)) {
                return $runner->validateTarget($command);
            }
        }

        return false;
    }

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface && $runner->supports($command)) {
                $runner->run($this->commandPreprocessor->process($command, $values), $values, $driver);
                break;
            }
        }
    }

    protected function getCommands(string $method = 'getAllCommands'): array
    {
        $commands = [];
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface) {
                $commands = array_merge($commands, call_user_func([$runner, $method]));
            }
        }

        return $commands;
    }
}
