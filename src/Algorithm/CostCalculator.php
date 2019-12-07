<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Exception;
use Symfony\Component\Workflow\Workflow;

class CostCalculator
{
    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @var float
     */
    protected $averageCost;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
        $this->averageCost = $this->calculateAverageCost();
    }

    public function estimateCost(Node $start, Node $end): float
    {
        $differentPlacesCount = (count($start->getPlaces()) + count($end->getPlaces()) - count(array_intersect($start->getPlaces(), $end->getPlaces())));

        return $differentPlacesCount * $this->averageCost;
    }

    public function getRealCost(Node $node, Node $adjacent): float
    {
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            if ($transition->getName() === $adjacent->getTransition()) {
                $metadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);

                return (float) ($metadata['weight'] ?? 1);
            }
        }
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            $places = array_unique(array_merge(array_diff($node->getPlaces(), $transition->getFroms()), $transition->getTos()));
            if (!array_diff($places, $adjacent->getPlaces()) && !array_diff($adjacent->getPlaces(), $places)) {
                // Goal node
                $adjacent->setTransition($transition->getName());
                $metadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);

                return (float) ($metadata['weight'] ?? 1);
            }
        }

        throw new Exception('Can not calculate real cost');
    }

    protected function calculateAverageCost(): float
    {
        $totalCost = 0;
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            $metadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
            if (isset($metadata['weight']) && $metadata['weight'] < 0) {
                throw new Exception(sprintf('Weight of transition %s should not less than zero', $transition->getName()));
            }
            $totalCost += $metadata['weight'] ?? 1;
        }

        return $totalCost / count($this->workflow->getDefinition()->getTransitions());
    }
}
