<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandRunnerInterface
{
    public const TAG = 'mbt_bundle.command_runner';

    public function getActions(): array;

    public function getAssertions(): array;

    public function supports(CommandInterface $command): bool;

    public function run(CommandInterface $command, RemoteWebDriver $driver): void;
}
