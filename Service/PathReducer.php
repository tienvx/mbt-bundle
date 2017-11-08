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
        if ($path->getVertices()->count() <= 2) {
            // There is no way to reduce a path that have less than or equals 2 nodes.
            return $path;
        }

        $try = $path->getVertices()->count();
        while ($try > 0) {
            $newPath = $this->tryToFindNewPath($path, $model, $throwable);
            if ($newPath) {
                // The shorter the path is, the less times we need to try.
                $path = $newPath;
                $try = $path->getVertices()->count();
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
        $startVertex = $path->getVertices()->getVertexFirst();
        // Get first random vertex and second random vertex. We can't use getVertexOrder(Vertices::ORDER_RANDOM) because
        // it does not return random key.
        $verticesVector = $path->getVertices()->getVector();
        $firstVertexIndex = $secondVertexIndex = 0;
        // Exclude the last vertex and the last edge, because they will always in the reproduce path (at the end).
        while ($firstVertexIndex === $secondVertexIndex || ($firstVertexIndex === $path->getVertices()->count() - 1) ||
            ($secondVertexIndex === $path->getVertices()->count() - 1)) {
            $firstVertexIndex = array_rand($verticesVector);
            $secondVertexIndex = array_rand($verticesVector);
        }
        if ($firstVertexIndex > $secondVertexIndex) {
            $tempIndex = $firstVertexIndex;
            $firstVertexIndex = $secondVertexIndex;
            $secondVertexIndex = $tempIndex;
        }
        $firstVertex = $verticesVector[$firstVertexIndex];
        $secondVertex = $verticesVector[$secondVertexIndex];

        // Remove any edges between first vertex and second vertex.
        $algorithm = new Dijkstra($firstVertex);
        $middleEdges = $algorithm->getEdgesTo($secondVertex)->getVector();
        $beginEdges = [];
        $endEdges = [];
        foreach ($path->getEdges() as $index => $edge) {
            if ($index < $firstVertexIndex) {
                $beginEdges[] = $edge;
            }
            elseif ($index < $secondVertexIndex) {
                // Ignore, replaced by middle edges.
            }
            else {
                $endEdges[] = $edge;
            }
        }
        $edges = array_merge($beginEdges, $middleEdges, $endEdges);
        $newPath = Path::factoryFromEdges($edges, $startVertex);
        return $newPath;
    }
}
