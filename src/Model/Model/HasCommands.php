<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

trait HasCommands
{
    protected array $commands = [];

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function setCommands(array $commands): void
    {
        $this->commands = [];

        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    protected function isSameCommands(array $commands): bool
    {
        if (count($this->commands) !== count($commands)) {
            return false;
        }
        foreach ($commands as $index => $command) {
            $thisCommand = $this->commands[$index] ?? null;
            if (
                !$thisCommand instanceof CommandInterface ||
                !$command instanceof CommandInterface ||
                !$thisCommand->isSame($command)
            ) {
                return false;
            }
        }

        return true;
    }
}
