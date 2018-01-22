<?php

namespace Tienvx\Bundle\MbtBundle\Algorithm;

use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Edge\Base as Edge;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\Algorithms\Eulerian as BaseEulerian;

class Eulerian extends BaseEulerian
{
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
            $resultGraph = $this->graph->createGraphClone();
            foreach ($resultGraph->getVertices() as $vertex) {
                $balance = 0;
                $minWeightEdgeIn = $minWeightEdgeOut = null;
                foreach ($vertex->getEdges() as $edge) {
                    if ($edge->hasVertexTarget($vertex)) {
                        $balance++;
                        if (is_null($minWeightEdgeIn) || $minWeightEdgeIn->getWeight() < $edge->getWeight()) {
                            $minWeightEdgeIn = $edge;
                        }
                    }
                    elseif ($edge->hasVertexStart($vertex)) {
                        $balance--;
                        if (is_null($minWeightEdgeOut) || $minWeightEdgeOut->getWeight() < $edge->getWeight()) {
                            $minWeightEdgeOut = $edge;
                        }
                    }
                }

                while ($balance !== 0) {
                    if ($balance > 0) {
                        $edge = $vertex->createEdgeTo($minWeightEdgeOut->getVertexEnd());
                        $edge->setWeight($minWeightEdgeOut->getWeight());
                        $edge->setAttribute('name', $minWeightEdgeOut->getAttribute('name'));
                        $edge->setAttribute('label', $minWeightEdgeOut->getAttribute('label'));
                        $balance--;
                    } elseif ($balance < 0) {
                        $edge = $minWeightEdgeIn->getVertexStart()->createEdgeTo($vertex);
                        $edge->setWeight($minWeightEdgeIn->getWeight());
                        $edge->setAttribute('name', $minWeightEdgeIn->getAttribute('name'));
                        $edge->setAttribute('label', $minWeightEdgeIn->getAttribute('label'));
                        $balance++;
                    }
                }
            }

            // Then get Euler path.
            $vertex = $resultGraph->getVertices()->getVertexFirst();
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
