<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Throwable;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

class ExploreStepsRunner extends StepsRunner
{
    protected array $steps;

    public function __construct(
        SelenoidHelperInterface $selenoidHelper,
        StepRunnerInterface $stepRunner,
        protected ConfigInterface $config
    ) {
        parent::__construct($selenoidHelper, $stepRunner);
    }

    protected function start(DebugInterface $entity): RemoteWebDriver
    {
        $this->steps = [];

        return parent::start($entity);
    }

    protected function catchException(callable $handleException, Throwable $throwable, ?StepInterface $step): void
    {
        if ($step instanceof StepInterface) {
            // Last step cause the bug, we can't capture it. We capture it here.
            $this->steps[] = clone $step;
        }
        $handleException($throwable, $this->steps);
    }

    protected function stop(?RemoteWebDriver $driver): void
    {
        parent::stop($driver);
        $this->steps = [];
    }

    protected function runStep(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): bool
    {
        parent::runStep($step, $revision, $driver);
        $this->steps[] = clone $step;

        return count($this->steps) < $this->config->getMaxSteps();
    }
}
