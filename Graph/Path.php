<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;

class Path extends Walk
{
    public static function factoryFromEdges($edges, Vertex $startVertex)
    {
        $vertices = array($startVertex);
        $vertexCurrent = $startVertex;
        foreach ($edges as $edge) {
            $vertexCurrent = $edge->getVertexToFrom($vertexCurrent);
            $vertices []= $vertexCurrent;
        }

        return new self($vertices, $edges);
    }

    public function equals(Path $path): bool
    {
        $verticesVector = $path->getVertices()->getVector();
        foreach ($this->vertices->getVector() as $index => $vertex) {
            if (!isset($verticesVector[$index]) || $verticesVector[$index]->getId() !== $vertex->getId()) {
                return false;
            }
        }

        $edgesVector = $path->getEdges()->getVector();
        foreach ($this->edges->getVector() as $index => $edge) {
            if (!isset($edgesVector[$index]) || $edgesVector[$index]->getAttribute('name') !== $edge->getAttribute('name')) {
                return false;
            }
        }

        return true;
    }
}
