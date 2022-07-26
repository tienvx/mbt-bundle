<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Builder;

use Generator;
use JMGQ\AStar\AStar;
use SingleColorPetrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Exception\OutOfRangeException;
use Tienvx\Bundle\MbtBundle\Exception\StepsNotConnectedException;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelperInterface;

class ShortestPathStepsBuilder implements StepsBuilderInterface
{
    public function __construct(
        protected PetrinetHelperInterface $petrinetHelper,
        protected GuardedTransitionServiceInterface $transitionService,
        protected MarkingHelperInterface $markingHelper
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(BugInterface $bug, int $from, int $to): Generator
    {
        yield from array_slice($bug->getSteps(), 0, $from);
        $petrinet = $this->petrinetHelper->build($bug->getTask()->getModelRevision());
        $shortestSteps = $this->getShortestSteps($bug->getSteps(), $from, $to, $petrinet);
        $lastStep = end($shortestSteps);
        reset($shortestSteps);
        yield from $shortestSteps;
        yield from $this->getRemainingSteps(array_slice($bug->getSteps(), $to + 1), $lastStep, $petrinet);
    }

    protected function getShortestSteps(array $steps, int $from, int $to, PetrinetInterface $petrinet): iterable
    {
        $fromStep = $steps[$from] ?? null;
        $toStep = $steps[$to] ?? null;

        if (!$fromStep instanceof StepInterface || !$toStep instanceof StepInterface) {
            throw new OutOfRangeException('Can not create shortest steps between invalid range');
        }

        return (new AStar(new PetrinetDomainLogic($this->transitionService, $this->markingHelper, $petrinet)))->run(
            $fromStep,
            $toStep
        );
    }

    protected function getRemainingSteps(array $steps, StepInterface $lastStep, PetrinetInterface $petrinet): iterable
    {
        $marking = $this->markingHelper->getMarking($petrinet, $lastStep->getPlaces(), $lastStep->getColor());
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                throw new OutOfRangeException('Remaining steps contains invalid step');
            }
            $transition = $petrinet->getTransitionById($step->getTransition());
            if (!$this->transitionService->isEnabled($transition, $marking)) {
                throw new StepsNotConnectedException('Can not connect remaining steps');
            }
            $this->transitionService->fire($transition, $marking);
            yield new Step(
                $this->markingHelper->getPlaces($marking),
                $marking->getColor(),
                $step->getTransition()
            );
        }
    }
}
