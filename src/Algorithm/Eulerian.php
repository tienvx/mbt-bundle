<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\Algorithms\Eulerian as BaseEulerian;
use Graphp\Algorithms\ShortestPath\Dijkstra;

class Eulerian extends BaseEulerian
{
    /**
     * Get best path (list of edges) connecting all edges.
     *
     * @param Vertex $startVertex
     * @return Edges
     */
    public function getEdges(Vertex $startVertex)
    {
        $edges = [];
        $components = new ConnectedComponents($this->graph);
        if ($components->isSingle()) {
            $resultGraph = $this->getResultGraph();

            // Then get Euler path.
            $vertex = $resultGraph->getVertex($startVertex->getId());
            /** @var Directed $edge */
            while ($edge = $this->getUnvisitedEdge($vertex)) {
                $edges[] = $edge;
                $edge->setAttribute('visited', true);
                $vertex = $edge->getVertexEnd();
            }
        }

        return new Edges($edges);
    }

    protected function getResultGraph(): Graph
    {
        // Get balance info.
        $balanceMap = [];
        $resultGraph = $this->graph->createGraphClone();
        foreach ($resultGraph->getVertices() as $vertex) {
            $balance = 0;
            /** @var Directed $edge */
            foreach ($vertex->getEdges() as $edge) {
                if ($edge->hasVertexTarget($vertex)) {
                    $balance++;
                }
                if ($edge->hasVertexStart($vertex)) {
                    $balance--;
                }
            }
            $balanceMap[$vertex->getId()] = $balance;
        }
        asort($balanceMap);

        // Balance the graph.
        while (reset($balanceMap) < 0 && end($balanceMap) > 0) {
            reset($balanceMap);
            $first = key($balanceMap);
            end($balanceMap);
            $last = key($balanceMap);
            $algorithm = new Dijkstra($this->graph->getVertex($last));
            $edges = $algorithm->getEdgesTo($this->graph->getVertex($first))->getVector();
            foreach ($edges as $edge) {
                $vertexStart = $resultGraph->getVertex($edge->getVertexStart()->getId());
                $vertexEnd = $resultGraph->getVertex($edge->getVertexEnd()->getId());
                $cloneEdge = $vertexStart->createEdgeTo($vertexEnd);
                $cloneEdge->setWeight($edge->getWeight());
                $cloneEdge->setAttribute('name', $edge->getAttribute('name'));
                $cloneEdge->setAttribute('label', $edge->getAttribute('label'));
                $cloneEdge->setAttribute('probability', $edge->getAttribute('probability'));
            }
            $balanceMap[$first]++;
            $balanceMap[$last]--;
            asort($balanceMap);
        }

        return $resultGraph;
    }

    protected function getUnvisitedEdge(Vertex $vertex): ?Edge
    {
        try {
            return $vertex->getEdges()->getEdgeMatch(function (Edge $edge) use ($vertex) {
                return $edge->hasVertexStart($vertex) && !$edge->getAttribute('visited');
            });
        } catch (UnderflowException $e) {
            return null;
        }
    }
}
