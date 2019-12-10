<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Exception;
use JMGQ\AStar\AStar as AbstractAStar;
use JMGQ\AStar\Node as NodeInterface;
use Symfony\Component\Workflow\Definition;

class AStar extends AbstractAStar
{
    /**
     * @var Definition
     */
    protected $definition;

    /**
     * @var CostCalculator
     */
    protected $costCalculator;

    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
        $this->costCalculator = new CostCalculator($definition);
    }

    public function calculateEstimatedCost(NodeInterface $start, NodeInterface $end): float
    {
        if (!$start instanceof Node || !$end instanceof Node) {
            throw new Exception('The provided nodes are invalid');
        }

        return $this->costCalculator->estimateCost($start, $end);
    }

    public function calculateRealCost(NodeInterface $node, NodeInterface $adjacent): float
    {
        if (!$node instanceof Node || !$adjacent instanceof Node) {
            throw new Exception('The provided nodes are invalid');
        }

        return $this->costCalculator->getRealCost($node, $adjacent);
    }

    public function generateAdjacentNodes(NodeInterface $node): array
    {
        if (!$node instanceof Node) {
            throw new Exception('The provided node is invalid');
        }
        $adjacents = [];
        foreach ($this->definition->getTransitions() as $transition) {
            if (count($transition->getFroms()) === count(array_intersect($node->getPlaces(), $transition->getFroms()))) {
                $places = array_unique(array_merge(array_diff($node->getPlaces(), $transition->getFroms()), $transition->getTos()));
                $adjacents[] = new Node($places, $transition->getName());
            }
        }

        return $adjacents;
    }
}
