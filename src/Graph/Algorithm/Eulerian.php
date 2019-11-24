<?php

namespace Tienvx\Bundle\MbtBundle\Graph\Algorithm;

use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\Algorithms\Eulerian as BaseEulerian;

class Eulerian extends BaseEulerian
{
    /**
     * Get best path (list of edges) connecting all edges.
     */
    public function getEdges(Vertex $startVertex): Edges
    {
        $edges = [];
        if ($this->singleComponent()) {
            $balancedGraph = $this->balanceGraph();

            // Then get Euler path.
            $vertex = $balancedGraph->getVertex($startVertex->getId());
            /** @var Directed $edge */
            while ($edge = $this->getUnvisitedEdge($vertex)) {
                $edges[] = $edge;
                $edge->setAttribute('visited', true);
                $vertex = $edge->getVertexEnd();
            }
        }

        return new Edges($edges);
    }

    protected function singleComponent(): bool
    {
        $components = new ConnectedComponents($this->graph);

        return $components->isSingle();
    }

    protected function balanceGraph(): Graph
    {
        $balancer = new GraphBalancer($this->graph);

        return $balancer->balance();
    }

    protected function getUnvisitedEdge(Vertex $vertex): ?Edge
    {
        try {
            return $vertex->getEdges()->getEdgeMatch(static function (Edge $edge) use ($vertex) {
                return $edge->hasVertexStart($vertex) && !$edge->getAttribute('visited');
            });
        } catch (UnderflowException $exception) {
            return null;
        }
    }
}
