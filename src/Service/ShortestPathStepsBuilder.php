<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
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
    public function create(BugInterface $bug, int $from, int $to): iterable
    {
        foreach ($bug->getSteps() as $index => $step) {
            if ($index <= $from) {
                yield $step;
            }
        }

        yield from $this->getSteps($bug, $from, $to);

        foreach ($bug->getSteps() as $index => $step) {
            if ($index > $to) {
                yield $step;
            }
        }
    }

    protected function getSteps(BugInterface $bug, int $from, int $to): iterable
    {
        $fromStep = $bug->getSteps()[$from] ?? null;
        $toStep = $bug->getSteps()[$to] ?? null;

        if (!$fromStep instanceof StepInterface || !$toStep instanceof StepInterface) {
            throw new OutOfRangeException('Can not create new steps using invalid range');
        }

        return $this->strategy->run($bug->getModel()->getPetrinet(), $fromStep, $toStep);
    }
}
