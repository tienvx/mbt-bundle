<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\GraphWithAttributes;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;

class WorkflowStrategy implements StrategyInterface
{
    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function build(): Graph
    {
        $graph = new Graph();
        do {
            $newVertices = 0;
            foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
                $newVertices += $this->createVertices($graph, $transition);
            }
        } while ($newVertices > 0);

        return $this->cleanUpGraph($graph);
    }

    /**
     * Clean up vertices and edges that never appear in the path.
     */
    protected function cleanUpGraph(Graph $graph): Graph
    {
        $initVertex = $graph->getVertex(VertexId::fromPlaces($this->workflow->getDefinition()->getInitialPlaces()));
        $components = new ConnectedComponents($graph);

        return $components->createGraphComponentVertex($initVertex);
    }

    protected function createVerticesAndEdge(Graph $graph, Transition $transition, array $froms, array $tos): int
    {
        $newVertices = 0;
        $from = VertexId::fromPlaces($froms);
        $to = VertexId::fromPlaces($tos);

        $newVertices += $this->createVertex($graph, $from, $froms);
        $newVertices += $this->createVertex($graph, $to, $tos);

        $this->createEdge($graph, $transition, $from, $to);

        return $newVertices;
    }

    protected function createVertices(Graph $graph, Transition $transition): int
    {
        $newVertices = 0;
        $froms = $transition->getFroms();
        $tos = $transition->getTos();
        sort($froms);
        sort($tos);

        $newVertices += $this->createDirectedVertices($graph, $transition, $froms, $tos);
        $newVertices += $this->createRelatedVertices($graph, $transition, $froms, $tos);

        return $newVertices;
    }

    protected function createDirectedVertices(Graph $graph, Transition $transition, array $froms, array $tos): int
    {
        return $this->createVerticesAndEdge($graph, $transition, $froms, $tos);
    }

    protected function createRelatedVertices(Graph $graph, Transition $transition, array $froms, array $tos): int
    {
        $newVertices = 0;
        $vertices = $graph->getVertices()->getVerticesMatch(static function (Vertex $vertex) use ($froms) {
            $vertexPlaces = $vertex->getAttribute('places');
            $intersect = array_intersect($vertexPlaces, $froms);

            return array_diff($vertexPlaces, $froms) && count($intersect) === count($froms) && !array_diff($intersect, $froms);
        });
        foreach ($vertices as $vertex) {
            $newFroms = $vertex->getAttribute('places');
            $newTos = array_unique(array_merge(array_diff($newFroms, $froms), $tos));
            sort($newFroms);
            sort($newTos);

            $newVertices += $this->createVerticesAndEdge($graph, $transition, $newFroms, $newTos);
        }

        return $newVertices;
    }

    protected function createVertex(Graph $graph, string $name, array $places): int
    {
        $newVertices = 0;
        $graphWithAttributes = new GraphWithAttributes($graph, $this->workflow);

        if (!$graph->hasVertex($name)) {
            $graphWithAttributes->createVertex($name, $places);
            $newVertices = 1;
        }

        return $newVertices;
    }

    protected function createEdge(Graph $graph, Transition $transition, string $from, string $to): void
    {
        $graphWithAttributes = new GraphWithAttributes($graph, $this->workflow);

        // TODO: support 2 different transitions but has exactly same froms and tos.
        if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
            $graphWithAttributes->createEdge($transition, $from, $to);
        }
    }
}
