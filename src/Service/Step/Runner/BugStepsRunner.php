<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\Color;
use SingleColorPetrinet\Model\ColorInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\DebugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;

class BugStepsRunner extends StepsRunner
{
    protected ?ColorInterface $lastColor;

    protected function start(DebugInterface $entity): RemoteWebDriver
    {
        $this->lastColor = new Color();

        return parent::start($entity);
    }

    protected function stop(?RemoteWebDriver $driver): void
    {
        parent::stop($driver);
        $this->lastColor = null;
    }

    protected function runStep(StepInterface $step, RevisionInterface $revision, RemoteWebDriver $driver): void
    {
        $color = clone $step->getColor();
        $step->setColor($this->lastColor);
        parent::runStep($step, $revision, $driver);
        $this->lastColor = $color;
    }

    protected function catchException(callable $handleException, Throwable $throwable, ?StepInterface $step): void
    {
        $handleException($throwable);
    }
}
