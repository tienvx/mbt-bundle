<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Generator;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class ShortestPathStepsBuilder implements StepsBuilderInterface
{
    protected PetrinetHelperInterface $petrinetHelper;
    protected ShortestPathStrategyInterface $strategy;

    public function __construct(
        PetrinetHelperInterface $petrinetHelper,
        ShortestPathStrategyInterface $strategy
    ) {
        $this->petrinetHelper = $petrinetHelper;
        $this->strategy = $strategy;
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(BugInterface $bug, int $from, int $to): Generator
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

        return $this->strategy->run(
            $this->petrinetHelper->build($bug->getTask()->getModelRevision()),
            $fromStep,
            $toStep
        );
    }
}
