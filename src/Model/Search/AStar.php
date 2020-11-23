<?php

namespace Tienvx\Bundle\MbtBundle\Model\Search;

use Exception;
use JMGQ\AStar\AStar as AbstractAStar;
use JMGQ\AStar\Node as NodeInterface;
use Petrinet\Model\PetrinetInterface;
use Petrinet\Model\TransitionInterface;
use SingleColorPetrinet\Model\ColorfulMarkingInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelperInterface;

class AStar extends AbstractAStar
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected MarkingHelperInterface $markingHelper;
    protected PetrinetInterface $petrinet;

    public function setTransitionService(GuardedTransitionServiceInterface $transitionService): void
    {
        $this->transitionService = $transitionService;
    }

    public function setMarkingHelper(MarkingHelperInterface $markingHelper): void
    {
        $this->markingHelper = $markingHelper;
    }

    public function setPetrinet(PetrinetInterface $petrinet): void
    {
        $this->petrinet = $petrinet;
    }

    /**
     * @throws Exception
     */
    public function calculateEstimatedCost(NodeInterface $start, NodeInterface $end): float
    {
        if (!$start instanceof Node || !$end instanceof Node) {
            throw new Exception('The provided nodes are invalid');
        }

        $tokensDiff = [];
        foreach ($end->getPlaces() as $place => $tokens) {
            $tokensDiff[$place] = abs($tokens - ($start->getPlaces()[$place] ?? 0));
        }
        // Estimate it will took N transitions to move N tokens if color is the same, twice if color is not the same.
        return array_sum($tokensDiff) * (($start->getColor() != $end->getColor()) + 1);
    }

    /**
     * @return int
     */
    public function calculateRealCost(NodeInterface $node, NodeInterface $adjacent)
    {
        // It only took 1 transition move N tokens from a node to adjacent node.
        return 1;
    }

    /**
     * @throws Exception
     */
    public function generateAdjacentNodes(NodeInterface $node): array
    {
        if (!$node instanceof Node) {
            throw new Exception('The provided node is invalid');
        }

        $adjacents = [];
        $marking = $this->markingHelper->getMarking($this->petrinet, $node->getPlaces());
        foreach ($this->transitionService->getEnabledTransitions($this->petrinet, $marking) as $transition) {
            // Create new marking.
            $marking = $this->markingHelper->getMarking($this->petrinet, $node->getPlaces());
            if ($transition instanceof TransitionInterface && $marking instanceof ColorfulMarkingInterface) {
                $this->transitionService->fire($transition, $marking);
                $adjacents[] = new Node($this->markingHelper->getPlaces($marking), $marking->getColor()->getColor(), $transition->getId());
            }
        }

        return $adjacents;
    }
}
