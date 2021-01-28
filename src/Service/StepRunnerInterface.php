<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

interface StepRunnerInterface
{
    public function run(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): void;
}
