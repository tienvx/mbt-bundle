<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

class PathReducer
{
    /**
     * @var PathRunner
     */
    protected $runner;

    public function __construct(PathRunner $runner)
    {
        $this->runner = $runner;
    }

    public function reduce(Path $path, Model $model, \Throwable $throwable): Path
    {
        $distance = $path->countVertices() - 1;

        while ($distance > 0) {
            $pairs = [];
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                for ($j = $path->countVertices() - 1; $j > $i; $j--) {
                    if ($path->getVertexAt($i)->getId() === $path->getVertexAt($j)->getId()) {
                        $pairs[] = [$i, $j];
                    }
                }
            }
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                for ($j = $path->countVertices() - 1; $j > $i; $j--) {
                    // Ignore 2 vertices are near in the path, it does not worth to reduce the path.
                    if ($path->getVertexAt($i)->getId() !== $path->getVertexAt($j)->getId() && $distance > 1 && ($j - $i) === $distance) {
                        $pairs[] = [$i, $j];
                    }
                }
            }
            foreach ($pairs as $pair) {
                list($i, $j) = $pair;
                $newPath = $this->getNewPath($path, $i, $j);
                // Make sure new path walkable.
                if ($this->runner->canWalk($newPath, $model)) {
                    try {
                        $this->runner->run($newPath, $model);
                    } catch (\Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $throwable->getMessage()) {
                            $path = $newPath;
                            $distance = $path->countVertices() - 1;
                            break;
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce reproduce path (any more).
        return $path;
    }

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
            $middleData = array_fill(0, count($middleEdges), null);
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
