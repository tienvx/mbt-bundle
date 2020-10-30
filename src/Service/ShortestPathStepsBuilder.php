<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Entity\Bug\Steps;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class ShortestPathStepsBuilder implements StepsBuilderInterface
{
    protected ShortestPathStrategyInterface $strategy;

    public function __construct(ShortestPathStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(BugInterface $bug, int $from, int $to): StepsInterface
    {
        $steps = new Steps();

        foreach ($bug->getSteps()->getSteps() as $index => $step) {
            if ($index <= $from) {
                $steps->addStep($step);
            }
        }

        foreach ($this->getSteps($bug, $from, $to) as $step) {
            if ($step instanceof StepInterface) {
                $steps->addStep($step);
            }
        }

        foreach ($bug->getSteps()->getSteps() as $index => $step) {
            if ($index > $to) {
                $steps->addStep($step);
            }
        }

        return $steps;
    }

    protected function getSteps(BugInterface $bug, int $from, int $to): iterable
    {
        $fromStep = $bug->getSteps()->getSteps()[$from] ?? null;
        $toStep = $bug->getSteps()->getSteps()[$to] ?? null;

        if (!$fromStep instanceof StepInterface || !$toStep instanceof StepInterface) {
            throw new OutOfRangeException('Can not create new steps using invalid range');
        }

        return $this->strategy->run($bug->getModel()->getPetrinet(), $fromStep, $toStep);
    }
}
