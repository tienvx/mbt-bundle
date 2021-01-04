<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandRunnerInterface
{
    public function getActions(): array;

    public function getAssertions(): array;

    public function supports(CommandInterface $command): bool;

    public function run(CommandInterface $command, RemoteWebDriver $driver): void;
}