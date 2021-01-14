<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

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

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void
    {
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface && $runner->supports($command)) {
                $runner->run($this->commandPreprocessor->process($command, $color), $color, $driver);
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
