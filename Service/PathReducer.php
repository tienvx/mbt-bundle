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
        if ($path->countVertices() <= 2) {
            // There is no way to reduce a path that have less than or equals 2 nodes.
            return $path;
        }

        $try = $path->countVertices();
        while ($try > 0) {
            $newPath = $this->tryToFindNewPath($path, $model, $throwable);
            if ($newPath) {
                // The shorter the path is, the less times we need to try.
                $path = $newPath;
                $try = $path->countVertices();
            }
            else {
                $try--;
            }
        }
        return $path;
    }

    protected function tryToFindNewPath(Path $path, Model $model, \Throwable $throwable): ?Path
    {
        $newPath = $this->randomNewPath($path);
        while (!$this->runner->canWalk($newPath, $model)) {
            $newPath = $this->randomNewPath($path);
        }

        $result = null;
        if (!$path->equals($newPath)) {
            try {
                $this->runner->run($newPath, $model);
            } catch (\Throwable $newThrowable) {
                if ($newThrowable->getMessage() === $throwable->getMessage()) {
                    $result = $newPath;
                }
            }
        }
        return $result;
    }

    protected function randomNewPath(Path $path)
    {
        $vertices = $path->getVertices();
        $edges = $path->getEdges();
        $allData = $path->getAllData();
        // Get first random vertex and second random vertex. We can't use getVertexOrder(Vertices::ORDER_RANDOM) because
        // it does not return (random) key.
        $firstVertexIndex = $secondVertexIndex = 0;
        // Exclude the last vertex and the last edge, because they will always in the reproduce path (at the end).
        while ($firstVertexIndex === $secondVertexIndex || ($firstVertexIndex === $path->countVertices() - 1) ||
            ($secondVertexIndex === $path->countVertices() - 1)) {
            $firstVertexIndex = array_rand($vertices);
            $secondVertexIndex = array_rand($vertices);
        }
        if ($firstVertexIndex > $secondVertexIndex) {
            $tempIndex = $firstVertexIndex;
            $firstVertexIndex = $secondVertexIndex;
            $secondVertexIndex = $tempIndex;
        }
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
