<?php

namespace Tienvx\Bundle\MbtBundle\Model\Model;

use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

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
            if ($command instanceof CommandInterface) {
                $this->addCommand($command);
            }
        }
    }

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }
}
