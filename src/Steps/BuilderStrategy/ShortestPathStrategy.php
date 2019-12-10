<?php

namespace Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy;

use Exception;
use Symfony\Component\Workflow\Definition;
use Tienvx\Bundle\MbtBundle\Algorithm\AStar;
use Tienvx\Bundle\MbtBundle\Algorithm\Node;
use Tienvx\Bundle\MbtBundle\Algorithm\Nodes;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class ShortestPathStrategy implements StrategyInterface
{
    /**
     * @var Definition
     */
    private $definition;

    public function __construct(Definition $definition)
    {
        $this->definition = $definition;
    }

    public function create(Steps $original, int $from, int $to): Steps
    {
        $fromPlaces = $original->getPlacesAt($from);
        $toPlaces = $original->getPlacesAt($to);
        if (!$fromPlaces || !$toPlaces) {
            throw new Exception('Can not create new path with shortest path');
        }

        $replaceStrategy = new ReplaceStrategy($this->getMiddleSteps($fromPlaces, $toPlaces));

        return $replaceStrategy->create($original, $from, $to);
    }

    protected function getMiddleSteps(array $fromPlaces, array $toPlaces): array
    {
        $samePlaces = !array_diff($fromPlaces, $toPlaces) && !array_diff($toPlaces, $fromPlaces);
        if ($samePlaces) {
            return [];
        }

        return Nodes::toSteps($this->shortestPath($fromPlaces, $toPlaces));
    }

    protected function shortestPath(array $fromPlaces, array $toPlaces): array
    {
        $start = new Node($fromPlaces);
        $goal = new Node($toPlaces);
        $aStar = new AStar($this->definition);

        return $aStar->run($start, $goal);
    }
}
