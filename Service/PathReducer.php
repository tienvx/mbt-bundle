<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Walk;
use Graphp\Algorithms\ShortestPath\Dijkstra;
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

    public function reduce(Walk $walk, Model $model, \Throwable $throwable): Walk
    {
        if ($walk->getVertices()->count() <= 2) {
            // There is no way to reduce a path that have less than or equals 2 nodes.
            return $walk;
        }

        $try = $walk->getVertices()->count();
        while ($try > 0) {
            $newWalk = $this->tryToFindNewWalk($walk, $model, $throwable);
            if ($newWalk) {
                // The shorter the walk is, the less times we need to try.
                $walk = $newWalk;
                $try = $walk->getVertices()->count();
            }
            else {
                $try--;
            }
        }
        return $walk;
    }

    protected function tryToFindNewWalk(Walk $walk, Model $model, \Throwable $throwable): ?Walk
    {
        $newWalk = $this->randomNewWalk($walk);
        while (!$this->runner->canWalk($newWalk, $model)) {
            $newWalk = $this->randomNewWalk($walk);
        }

        $result = null;
        try {
            $this->runner->run($newWalk, $model);
        } catch (\Throwable $newThrowable) {
            if ($newThrowable->getMessage() === $throwable->getMessage()) {
                $result = $newWalk;
            }
        }
        return $result;
    }

    protected function randomNewWalk(Walk $walk)
    {
        $startVertex = $walk->getVertices()->getVertexFirst();
        // Get first random vertex and second random vertex. We can't use getVertexOrder(Vertices::ORDER_RANDOM) because
        // it does not return random key.
        $verticesVector = $walk->getVertices()->getVector();
        $firstVertexIndex = $secondVertexIndex = 0;
        // Exclude the last vertex and the last edge, because they will always in the reproduce path (at the end).
        while ($firstVertexIndex === $secondVertexIndex || ($firstVertexIndex === $walk->getVertices()->count() - 1) ||
            ($secondVertexIndex === $walk->getVertices()->count() - 1)) {
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
        foreach ($walk->getEdges() as $index => $edge) {
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
        $newWalk = Walk::factoryFromEdges($edges, $startVertex);
        return $newWalk;
    }
}
