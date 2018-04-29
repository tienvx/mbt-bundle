<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\Path;

trait NewPathTrait
{
    protected function getNewPath(Path $path, $firstVertexIndex, $secondVertexIndex): Path
    {
        $allData = $path->getAllData();
        $firstVertex = $path->getVertexAt($firstVertexIndex);
        $secondVertex = $path->getVertexAt($secondVertexIndex);

        $beginEdges = [];
        $endEdges = [];
        $beginData = [];
        $endData = [];
        foreach ($path->getEdges() as $index => $edge) {
            if ($index < $firstVertexIndex) {
                $beginEdges[] = $edge;
                $beginData[] = $allData[$index];
            }
            elseif ($index >= $secondVertexIndex) {
                $endEdges[] = $edge;
                $endData[] = $allData[$index];
            }
        }

        if ($firstVertex->getId() !== $secondVertex->getId()) {
            // Replace all edges between first vertex and second vertex by algorithm.
            $algorithm = new Dijkstra($firstVertex);
            $middleEdges = $algorithm->getEdgesTo($secondVertex)->getVector();
            $middleData = array_fill(0, count($middleEdges), []);
        }
        else {
            // Remove all edges between first vertex and second vertex.
            $middleEdges = [];
            $middleData = [];
        }

        $edges = array_merge($beginEdges, $middleEdges, $endEdges);
        $newPath = Path::factoryFromEdges($edges, $path->getVertexAt(0));
        $allData = array_merge($beginData, $middleData, $endData);
        $newPath->setAllData($allData);
        return $newPath;
    }
}