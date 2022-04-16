<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

class TaskStepsRunner extends StepsRunner
{
    protected array $steps;
    protected ConfigInterface $config;

    public function __construct(
        SelenoidHelperInterface $selenoidHelper,
        StepRunnerInterface $stepRunner,
        ConfigInterface $config
    ) {
        parent::__construct($selenoidHelper, $stepRunner);
        $this->config = $config;
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
        $handleException($this->createBug($this->steps, $throwable->getMessage()));
    }

    protected function stop(?RemoteWebDriver $driver): void
    {
        parent::stop($driver);
        $this->steps = [];
    }

    protected function canStop(StepInterface $step): bool
    {
        $this->steps[] = clone $step;

        return count($this->steps) >= $this->config->getMaxSteps();
    }

    protected function createBug(array $steps, string $message): BugInterface
    {
        $bug = new Bug();
        $bug->setTitle('');
        $bug->setSteps($steps);
        $bug->setMessage($message);

        return $bug;
    }
}
