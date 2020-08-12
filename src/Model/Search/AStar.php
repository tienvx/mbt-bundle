<?php

namespace Tienvx\Bundle\MbtBundle\Model\Search;

use Exception;
use JMGQ\AStar\AStar as AbstractAStar;
use JMGQ\AStar\Node as NodeInterface;
use Petrinet\Model\PetrinetInterface;
use SingleColorPetrinet\Service\GuardedTransitionServiceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

class AStar extends AbstractAStar
{
    protected GuardedTransitionServiceInterface $transitionService;
    protected PetrinetInterface $petrinet;

    public function setTransitionService(GuardedTransitionServiceInterface $transitionService): void
    {
        $this->transitionService = $transitionService;
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

        $tokensCountByPlace = $start->countTokensByPlace();
        $tokensCountDiff = [];
        foreach ($end->countTokensByPlace() as $place => $tokens) {
            $tokensCountDiff[$place] = abs($tokens - ($tokensCountByPlace[$place] ?? 0));
        }
        // Estimate it will took N transitions to move N tokens if color is the same, twice if color is not the same.
        return array_sum($tokensCountDiff) * (($start->getMarking()->getColor()->toArray() != $end->getMarking()->getColor()->toArray()) + 1);
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
        foreach ($this->transitionService->getEnabledTransitions($this->petrinet, $node->getMarking()) as $transition) {
            if ($transition instanceof TransitionInterface) {
                $marking = clone $node->getMarking();
                $this->transitionService->fire($transition, $marking);
                $adjacents[] = new Node($marking, $transition);
            }
        }

        return $adjacents;
    }
}
