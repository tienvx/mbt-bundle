<?php

namespace Tienvx\Bundle\MbtBundle\Service\AStar;

use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Model\Bug\Step;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

class PetrinetDomainLogic implements PetrinetDomainLogicInterface
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected ?PetrinetInterface $petrinet = null;

    public function __construct(
        GuardedTransitionServiceInterface $transitionService,
        MarkingHelperInterface $markingHelper
    ) {
        $this->transitionService = $transitionService;
        $this->markingHelper = $markingHelper;
    }

    public function setPetrinet(?PetrinetInterface $petrinet): void
    {
        $this->petrinet = $petrinet;
    }

    public function calculateEstimatedCost(mixed $fromNode, mixed $toNode): float|int
    {
        if (!$fromNode instanceof Step || !$toNode instanceof Step) {
            throw new RuntimeException('The provided nodes are invalid');
        }

        $tokensDiff = 0;
        $fromPlaces = array_keys($fromNode->getPlaces());
        $toPlaces = array_keys($toNode->getPlaces());
        foreach (array_unique(array_merge($fromPlaces, $toPlaces)) as $place) {
            if (!in_array($place, $fromPlaces)) {
                $tokensDiff += $toNode->getPlaces()[$place];
            } elseif (!in_array($place, $toPlaces)) {
                $tokensDiff += $fromNode->getPlaces()[$place];
            } else {
                $tokensDiff += abs($toNode->getPlaces()[$place] - $fromNode->getPlaces()[$place]);
            }
        }
        // Estimate it will took N transitions to move N tokens if color is the same, twice if color is not the same.
        return $tokensDiff * (($fromNode->getColor()->getValues() != $toNode->getColor()->getValues()) + 1);
    }

    public function calculateRealCost(mixed $node, mixed $adjacent): float|int
    {
        // It only took 1 transition move N tokens from a node to adjacent node.
        return 1;
    }

    public function getAdjacentNodes(mixed $node): iterable
    {
        if (!$node instanceof Step) {
            throw new RuntimeException('The provided node is invalid');
        }

        if (!$this->petrinet instanceof PetrinetInterface) {
            throw new RuntimeException('Petrinet is required');
        }

        $adjacents = [];
        $marking = $this->markingHelper->getMarking($this->petrinet, $node->getPlaces(), $node->getColor());
        foreach ($this->transitionService->getEnabledTransitions($this->petrinet, $marking) as $transition) {
            // Create new marking.
            $marking = $this->markingHelper->getMarking($this->petrinet, $node->getPlaces(), $node->getColor());
            if ($transition instanceof TransitionInterface) {
                $this->transitionService->fire($transition, $marking);
                $adjacents[] = new Step(
                    $this->markingHelper->getPlaces($marking),
                    $marking->getColor(),
                    $transition->getId()
                );
            }
        }

        return $adjacents;
    }
}
