<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\Path;

trait NewPathTrait
{
    protected function getNewPath(Path $path, int $firstVertexIndex, int $secondVertexIndex): Path
    {
        $firstVertex = $path->getVertexAt($firstVertexIndex);
        $secondVertex = $path->getVertexAt($secondVertexIndex);

        $beginEdges = [];
        $endEdges = [];
        $beginData = [];
        $endData = [];
        foreach ($path->getEdges() as $index => $edge) {
            if ($index < $firstVertexIndex) {
                $beginEdges[] = $edge;
                $beginData[] = $path->getDataAt($index);
            } elseif ($index >= $secondVertexIndex) {
                $endEdges[] = $edge;
                $endData[] = $path->getDataAt($index);
            }
        }

        if ($firstVertex->getId() !== $secondVertex->getId()) {
            // Replace all edges between first vertex and second vertex by algorithm.
            $algorithm = new Dijkstra($firstVertex);
            $middleEdges = $algorithm->getEdgesTo($secondVertex)->getVector();
            $middleData = array_fill(0, count($middleEdges), null);
        } else {
            // Remove all edges between first vertex and second vertex.
            $middleEdges = [];
            $middleData = [];
        }

        $edges = array_merge($beginEdges, $middleEdges, $endEdges);
        $allData = array_merge($beginData, $middleData, $endData);
        $newPath = new Path($edges, $allData);
        return $newPath;
    }
}
