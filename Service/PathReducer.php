<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Walk;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Model\Model;

class PathReducer
{
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
            $newWalk = $this->findNewWalk($walk, $model, $throwable);
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

    protected function findNewWalk(Walk $walk, Model $model, \Throwable $throwable): ?Walk
    {
        $startVertex = $walk->getVertices()->getVertexFirst();
        // Get first random vertex and second random vertex. We can't use getVertexOrder(Vertices::ORDER_RANDOM) because
        // it does not return random key.
        $verticesVector = $walk->getVertices()->getVector();
        $firstVertexIndex = array_rand($verticesVector);
        $firstVertex = $verticesVector[$firstVertexIndex];
        $secondVertexIndex = array_rand($verticesVector);
        while ($firstVertexIndex >= $secondVertexIndex) {
            $secondVertexIndex = array_rand($verticesVector);
        }
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

        try {
            $this->runner->run($newWalk, $model);
        } catch (\Throwable $newThrowable) {
            if ($newThrowable === $throwable) {
                return $newWalk;
            }
        } finally {
            return null;
        }
    }
}