<?php

namespace Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy;

use Exception;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class ShortestPathStrategy implements StrategyInterface
{
    /**
     * @var Graph
     */
    private $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
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

    protected function getMiddleSteps(array $fromPlaces, array $toPlaces)
    {
        $samePlaces = !array_diff($fromPlaces, $toPlaces) && !array_diff($toPlaces, $fromPlaces);
        if ($samePlaces) {
            return [];
        }

        $middleSteps = [];
        foreach ($this->shortestPath(VertexId::fromPlaces($fromPlaces), VertexId::fromPlaces($toPlaces)) as $edge) {
            if ($edge instanceof Directed) {
                $middleSteps[] = new Step(
                    $edge->getAttribute('name', ''),
                    new Data(),
                    $edge->getVertexEnd()->getAttribute('places', [])
                );
            } else {
                throw new Exception('Only support directed graph');
            }
        }

        return $middleSteps;
    }

    protected function shortestPath(string $fromVertexId, string $toVertexId): Edges
    {
        $fromVertex = $this->graph->getVertex($fromVertexId);
        $toVertex = $this->graph->getVertex($toVertexId);
        $algorithm = new Dijkstra($fromVertex);

        return $algorithm->getEdgesTo($toVertex);
    }
}
