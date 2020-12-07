<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

interface CommandRunnerInterface
{
    public function run(CommandInterface $command, RemoteWebDriver $driver): void;
}
