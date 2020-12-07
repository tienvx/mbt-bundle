<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface StepRunnerInterface
{
    public function run(StepInterface $step, ModelInterface $model, RemoteWebDriver $driver): void;
}
