<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Exception;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Exception\OutOfBoundsException;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;

class Path
{
    /**
     *
     * @var array
     */
    protected $allData;

    /**
     *
     * @var Directed[]
     */
    protected $edges;

    public function __construct(array $edges = [], array $allData = [])
    {
        $this->edges    = $edges;
        $this->allData  = $allData;
    }

    public function addEdge(Directed $edge)
    {
        $this->edges[] = $edge;
    }

    public function addData(array $data)
    {
        $this->allData[] = $data;
    }

    public function countVertices()
    {
        return count($this->edges) + 1;
    }

    public function countEdges()
    {
        return count($this->edges);
    }

    /**
     * @return Vertex[]
     */
    public function getVertices()
    {
        $vertices = [];
        for ($i = 0; $i < count($this->edges); $i++) {
            if ($i === 0) {
                $vertices[] = $this->edges[$i]->getVertexStart();
            }
            $vertices[] = $this->edges[$i]->getVertexEnd();
        }
        return $vertices;
    }

    public function getVertexAt(int $index): Vertex
    {
        if ($index === 0) {
            return $this->edges[$index]->getVertexStart();
        }
        return $this->edges[$index - 1]->getVertexEnd();
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function getDataAt(int $index): ?array
    {
        return $this->allData[$index];
    }

    public function setDataAt(int $index, array $data)
    {
        $this->allData[$index] = $data;
    }

    public function __toString()
    {
        $steps = [];
        for ($i = 0; $i < count($this->edges); $i++) {
            if ($i === 0) {
                $steps[] = $this->edges[$i]->getVertexStart()->getAttribute('name');
            }
            $data = $this->allData[$i] ?? [];
            array_walk($data, function (&$value, $key) {
                $value = "$key=$value";
            });
            $steps[] = $this->edges[$i]->getAttribute('name') . '(' . implode(',', $data) . ')';
            $steps[] = $this->edges[$i]->getVertexEnd()->getAttribute('name');
        }
        return implode(' ', $steps);
    }

    /**
     * @param string $steps
     * @param Graph $graph
     * @return Path
     * @throws \Exception
     */
    public static function fromSteps(string $steps, Graph $graph): Path
    {
        $edges = [];
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
            } else {
                try {
                    $graph->getVertex($step);
                } catch (OutOfBoundsException $exception) {
                    throw new Exception(sprintf('%s is an invalid place', $step));
                }
            }
        }
        return new static($edges, $allData);
    }
}
