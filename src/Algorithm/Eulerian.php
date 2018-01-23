<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Edge\Base as Edge;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\Algorithms\Eulerian as BaseEulerian;
use Graphp\Algorithms\ShortestPath\Dijkstra;

class Eulerian extends BaseEulerian
{
    /**
     * @var Vertex
     */
    protected $startVertex;

    public function setStartVertex(Vertex $vertex)
    {
        $this->startVertex = $vertex;
    }

    /**
     * Get best path (list of edges) connecting all edges.
     *
     * @return Edges
     */
    public function getEdges()
    {
        $returnEdges = [];
        $components = new ConnectedComponents($this->graph);
        if ($components->isSingle()) {
            // Balance the graph.
            $balanceMap = [];
            $resultGraph = $this->graph->createGraphClone();
            foreach ($resultGraph->getVertices() as $vertex) {
                $balance = 0;
                foreach ($vertex->getEdges() as $edge) {
                    if ($edge->hasVertexTarget($vertex)) {
                        $balance++;
                    }
                    if ($edge->hasVertexStart($vertex)) {
                        $balance--;
                    }
                }
                $vertex->setBalance($balance);
                $balanceMap[$vertex->getId()] = $balance;
            }
            asort($balanceMap);

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
                }
                $balanceMap[$first]++;
                $balanceMap[$last]--;
                asort($balanceMap);
            }

            // Then get Euler path.
            $vertex = $resultGraph->getVertex($this->startVertex->getId());
            while ($edge = $this->getUnvisitedEdge($vertex)) {
                $returnEdges[] = $edge;
                $edge->setAttribute('visited', true);
                $vertex = $edge->getVertexEnd();
            }
        }

        return new Edges($returnEdges);
    }

    protected function getUnvisitedEdge(Vertex $vertex): ?Edge
    {
        try {
            return $vertex->getEdges()->getEdgeMatch(function (Edge $edge) use ($vertex) {
                return $edge->hasVertexStart($vertex) && !$edge->getAttribute('visited');
            });
        }
        catch (UnderflowException $e) {
            return null;
        }
    }
}
