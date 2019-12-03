<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Algorithm;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\BreadthFirst;

class GraphBalancer
{
    /**
     * @var Graph
     */
    protected $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function balance(): Graph
    {
        $graphClone = $this->graph->createGraphClone();
        $balanceInfo = new BalanceInfo($graphClone);
        $balanceCallback = function (string $firstVertexId, string $lastVertexId) use ($graphClone): void {
            $algorithm = new BreadthFirst($this->graph->getVertex($firstVertexId));
            $edges = $algorithm->getEdgesTo($this->graph->getVertex($lastVertexId))->getVector();
            foreach ($edges as $edge) {
                $this->cloneEdge($graphClone, $edge);
            }
        };

        while ($balanceInfo->canBalance()) {
            $balanceInfo->balance($balanceCallback);
        }

        return $graphClone;
    }

    protected function cloneEdge(Graph $graphClone, Directed $edge): void
    {
        $vertexStart = $graphClone->getVertex($edge->getVertexStart()->getId());
        $vertexEnd = $graphClone->getVertex($edge->getVertexEnd()->getId());
        $cloneEdge = $vertexStart->createEdgeTo($vertexEnd);
        $cloneEdge->setWeight($edge->getWeight());
        $cloneEdge->setAttribute('name', $edge->getAttribute('name'));
        $cloneEdge->setAttribute('label', $edge->getAttribute('label'));
        $cloneEdge->setAttribute('probability', $edge->getAttribute('probability'));
    }
}
