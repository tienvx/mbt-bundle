<?php

namespace Tienvx\Bundle\MbtBundle\Service;

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

        while ($distance > 1) {
            for ($i = 0; $i < $path->countVertices() - 1; $i++) {
                for ($j = $path->countVertices() - 1; $j >= ($i + $distance); $j--) {
                    $newPath = $this->getNewPath($path, $i, $j);
                    // Make sure new path walkable.
                    if ($this->runner->canWalk($newPath, $model)) {
                        try {
                            $this->runner->run($newPath, $model);
                        } catch (\Throwable $newThrowable) {
                            if ($newThrowable->getMessage() === $throwable->getMessage()) {
                                return $newPath;
                            }
                        }
                    }
                }
            }
            $distance--;
        }

        // Can not reduce path.
        return $path;
    }

    protected function getNewPath(Path $path, $firstVertexIndex, $secondVertexIndex): Path
    {
        $vertices = $path->getVertices();
        $edges = $path->getEdges();
        $allData = $path->getAllData();
        $firstVertex = $vertices[$firstVertexIndex];
        $secondVertex = $vertices[$secondVertexIndex];

        // Remove any edges between first vertex and second vertex.
        $algorithm = new Dijkstra($firstVertex);
        $beginEdges = [];
        $middleEdges = $algorithm->getEdgesTo($secondVertex)->getVector();
        $endEdges = [];
        $beginData = [];
        $middleData = array_fill(0, count($middleEdges), null);
        $endData = [];
        foreach ($edges as $index => $edge) {
            if ($index < $firstVertexIndex) {
                $beginEdges[] = $edge;
                $beginData[] = $allData[$index];
            }
            elseif ($index < $secondVertexIndex) {
                // Middle edges are replaced by algorithm.
            }
            else {
                $endEdges[] = $edge;
                $endData[] = $allData[$index];
            }
        }
        $edges = array_merge($beginEdges, $middleEdges, $endEdges);
        $newPath = Path::factoryFromEdges($edges, $vertices[0]);
        $allData = array_merge($beginData, $middleData, $endData);
        $newPath->setAllData($allData);
        return $newPath;
    }
}
