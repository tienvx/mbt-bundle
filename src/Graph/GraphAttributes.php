<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Edge\Directed as EdgeDirected;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Serializable;

class GraphAttributes implements Serializable
{
    /**
     * @var array
     */
    protected $data;

    protected function __construct(array $data)
    {
        $this->data = $data;
    }

    public function serialize(): string
    {
        return serialize($this->data);
    }

    public function unserialize($serialized): void
    {
        $this->data = unserialize($serialized);
    }

    public static function createVertex(Graph $graph, string $name): void
    {
        if (!$graph->hasVertex($name)) {
            $vertex = $graph->createVertex($name);
            $vertex->setAttribute('name', $name);
            $vertex->setAttribute('places', json_decode($name));
        }
    }

    public static function createEdge(Graph $graph, string $from, string $to, string $name, string $label, int $weight, int $probability): void
    {
        if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
            $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
            $edge->setAttribute('name', $name);

            $edge->setAttribute('label', $label);
            $edge->setWeight($weight);
            $edge->setAttribute('probability', $probability);
        }
    }

    public static function fromGraph(Graph $graph): self
    {
        $data = [0 => [], 1 => []];
        /** @var Vertex $vertex */
        foreach ($graph->getVertices() as $vertex) {
            $data[0][] = $vertex->getAttribute('name');
        }
        /** @var EdgeDirected $edge */
        foreach ($graph->getEdges() as $edge) {
            $data[1][] = [
                0 => $edge->getVertexStart()->getAttribute('name'),
                1 => $edge->getVertexEnd()->getAttribute('name'),
                2 => $edge->getAttribute('name'),
                3 => $edge->getAttribute('label'),
                4 => $edge->getWeight(),
                5 => $edge->getAttribute('probability'),
            ];
        }

        return new self($data);
    }

    public function toGraph(): Graph
    {
        $graph = new Graph();
        foreach ($this->data[0] as $name) {
            static::createVertex($graph, $name);
        }
        foreach ($this->data[1] as $edgeAttributes) {
            static::createEdge($graph, ...$edgeAttributes);
        }

        return $graph;
    }
}
