<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Exception;
use Symfony\Component\Workflow\Definition;

class CostCalculator
{
    /**
     * @var Definition
     */
    protected $definition;

    /**
     * @var float
     */
    protected $averageCost;

    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
        $this->averageCost = $this->calculateAverageCost();
    }

    public function estimateCost(Node $start, Node $end): float
    {
        $differentPlacesCount = (count($start->getPlaces()) + count($end->getPlaces()) - count(array_intersect($start->getPlaces(), $end->getPlaces())));

        return $differentPlacesCount * $this->averageCost;
    }

    public function getRealCost(Node $node, Node $adjacent): float
    {
        foreach ($this->definition->getTransitions() as $transition) {
            if ($transition->getName() === $adjacent->getTransition()) {
                $metadata = $this->definition->getMetadataStore()->getTransitionMetadata($transition);

                return (float) ($metadata['weight'] ?? 1);
            }
        }
        foreach ($this->definition->getTransitions() as $transition) {
            $places = array_unique(array_merge(array_diff($node->getPlaces(), $transition->getFroms()), $transition->getTos()));
            if (!array_diff($places, $adjacent->getPlaces()) && !array_diff($adjacent->getPlaces(), $places)) {
                // Goal node
                $adjacent->setTransition($transition->getName());
                $metadata = $this->definition->getMetadataStore()->getTransitionMetadata($transition);

                return (float) ($metadata['weight'] ?? 1);
            }
        }

        throw new Exception('Can not calculate real cost');
    }

    protected function calculateAverageCost(): float
    {
        $totalCost = 0;
        foreach ($this->definition->getTransitions() as $transition) {
            $metadata = $this->definition->getMetadataStore()->getTransitionMetadata($transition);
            if (isset($metadata['weight']) && $metadata['weight'] < 0) {
                throw new Exception(sprintf('Weight of transition %s should not less than zero', $transition->getName()));
            }
            $totalCost += $metadata['weight'] ?? 1;
        }

        return $totalCost / count($this->definition->getTransitions());
    }
}
