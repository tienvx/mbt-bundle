<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\ValuesInterface;

interface CommandRunnerManagerInterface
{
    public function getAllCommands(): array;

    public function getCommandsRequireTarget(): array;

    public function getCommandsRequireValue(): array;

    public function validateTarget(CommandInterface $command): bool;

    public function run(CommandInterface $command, ValuesInterface $values, RemoteWebDriver $driver): void;
}
