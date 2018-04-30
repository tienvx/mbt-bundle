<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Iterator;

class Path implements Iterator
{
    /**
     *
     * @var array
     */
    protected $allData;

    /**
     *
     * @var Vertex[]
     */
    protected $vertices;

    /**
     *
     * @var Directed[]
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
        $this->allData  = $data;
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
        $this->allData[] = $data;
    }

    public function countVertices()
    {
        return count($this->vertices);
    }

    public function countEdges()
    {
        return count($this->edges);
    }

    public function getVertices()
    {
        return $this->vertices;
    }

    public function getVertexAt(int $index): Vertex
    {
        return $this->vertices[$index] ?? null;
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function getAllData()
    {
        return $this->allData;
    }

    public function setAllData(array $allData)
    {
        $this->allData = $allData;
    }

    public function getDataAtPosition(int $position): array
    {
        if ($position % 2 === 1 && isset($this->allData[($position - 1) / 2])) {
            return $this->allData[($position - 1) / 2];
        }
        return [];
    }

    public function hasDataAtPosition(int $position)
    {
        return ($position % 2 === 1 && isset($this->allData[($position - 1) / 2]));
    }

    public function setDataAtPosition(int $position, array $data)
    {
        if ($position % 2 === 1) {
            $this->allData[($position - 1) / 2] = $data;
        }
    }

    /**
     * Code cloned from Fhaculty\Graph\Walk
     *
     * @param  Directed[]         $edges
     * @param  Vertex             $startVertex
     * @return Path
     */
    public static function factoryFromEdges($edges, Vertex $startVertex)
    {
        $vertices = [$startVertex];
        $vertexCurrent = $startVertex;
        foreach ($edges as $edge) {
            $vertexCurrent = $edge->getVertexToFrom($vertexCurrent);
            $vertices[] = $vertexCurrent;
        }

        return new self($vertices, $edges);
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
        return ($this->position >= 0) && ($this->position < (count($this->vertices) + count($this->edges)));
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function __toString()
    {
        $sequence = [];
        foreach ($this as $position => $step) {
            if ($step instanceof Vertex) {
                $sequence[] = $step->getAttribute('name');
            }
            else if ($step instanceof Directed) {
                $data = $this->getDataAtPosition($position);
                array_walk($data, function (&$value, $key) {
                    $value = "$key=$value";
                });
                $sequence[] = $step->getAttribute('name') . '(' . implode(',', $data) . ')';
            }
        }
        return implode(' ', $sequence);
    }

    public static function fromSteps(string $steps, Graph $graph): Path
    {
        $edges = [];
        $vertices = [];
        $allData = [];
        $steps = explode(' ', $steps);
        foreach ($steps as $index => $step) {
            if (preg_match('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\((.*)\)/', $step, $matches)) {
                $transition = $matches[1];
                $data = [];
                if ($matches[2]) {
                    $params = explode(',', $matches[2]);
                    foreach ($params as $param) {
                        list($key, $value) = explode('=', $param);
                        $data[$key] = $value;
                    }
                }
                $edge = $graph->getEdges()->getEdgeMatch(function (Directed $edge) use ($transition) {
                    return $edge->getAttribute('name') === $transition;
                });
                $allData[] = $data;
                $edges[] = $edge;
            }
            else {
                $vertex = $graph->getVertex($step);
                $vertices[] = $vertex;
            }
        }
        return new static($vertices, $edges, $allData);
    }
}
