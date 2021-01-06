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

    public function getActions(): array
    {
        $actions = [];
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface) {
                $actions += $runner->getActions();
            }
        }

        return $actions;
    }

    public function getAssertions(): array
    {
        $assertions = [];
        foreach ($this->runners as $runner) {
            if ($runner instanceof CommandRunnerInterface) {
                $assertions += $runner->getAssertions();
            }
        }

        return $assertions;
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
}
