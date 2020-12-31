<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandRunnerManagerInterface
{
    public function getActions(): array;

    public function getAssertions(): array;

    public function run(CommandInterface $command, RemoteWebDriver $driver): void;
}
