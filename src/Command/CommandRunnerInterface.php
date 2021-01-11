<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandRunnerInterface
{
    public const TAG = 'mbt_bundle.command_runner';

    public function getAllCommands(): array;

    public function getCommandsRequireTarget(): array;

    public function getCommandsRequireValue(): array;

    public function supports(CommandInterface $command): bool;

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void;
}