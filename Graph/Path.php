<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Iterator;

class Path implements Iterator
{
    /**
     *
     * @var array
     */
    protected $data;

    /**
     *
     * @var array
     */
    protected $vertices;

    /**
     *
     * @var array
     */
    protected $edges;

    /**
     *
     * @var int
     */
    protected $position;

    public function __construct(array $vertices = [], array $edges = [], array $data = [])
    {
        $this->vertices = $vertices;
        $this->edges    = $edges;
        $this->data     = $data;
        $this->position = 0;
    }

    public function addEdge(Directed $edge)
    {
        $this->edges[] = $edge;
    }

    public function addVertex(Vertex $vertex)
    {
        $this->vertices[] = $vertex;
    }

    public function addData(array $data)
    {
        $this->data[] = $data;
    }

    public function countVertices()
    {
        return count($this->vertices);
    }

    public function getVertices()
    {
        return $this->vertices;
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function getData(int $position)
    {
        if ($position % 2 === 1 && isset($this->data[($position - 1) / 2])) {
            return $this->data[($position - 1) / 2];
        }
        return null;
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

    public function current()
    {
        if ($this->position % 2 === 1) {
            return $this->edges[($this->position - 1) / 2];
        }
        else {
            return $this->vertices[$this->position / 2];
        }
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return ($this->position > 0) && ($this->position < (count($this->vertices) + count($this->edges)));
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
