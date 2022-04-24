<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Throwable;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

abstract class BugStepsRunner extends StepsRunner
{
    protected function catchException(callable $handleException, Throwable $throwable, ?StepInterface $step): void
    {
        $handleException($throwable);
    }
}
