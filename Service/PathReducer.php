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
        // Get start vertex, random first vertex and random second vertex.
        $startVertex = $walk->getVertices()->getVertexFirst();
        $firstVertex = $walk->getVertices()->getVertexOrder(Vertices::ORDER_RANDOM);
        $firstVertexIndex = $walk->getVertices()->getIndexVertex($firstVertex);
        $secondVertex = $walk->getVertices()->getVertexOrder(Vertices::ORDER_RANDOM);
        while ($firstVertex !== $secondVertex) {
            $secondVertex = $walk->getVertices()->getVertexOrder(Vertices::ORDER_RANDOM);
        }
        $secondVertexIndex = $walk->getVertices()->getIndexVertex($secondVertex);

        // Remove any edges between first vertex and second vertex.
        $algorithm = new Dijkstra($firstVertex);
        $middleEdges = $algorithm->getEdgesTo($secondVertex);
        $beginEdges = [];
        $endEdges = [];
        foreach ($walk->getEdges() as $index => $edge) {
            if ($index <= $firstVertexIndex) {
                $beginEdges[] = $edge;
            }
            elseif ($index <= $secondVertexIndex) {
                // Ignore, replace by middle edges.
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