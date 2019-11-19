<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
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

    protected function createVertex(Graph $graph, string $name, array $places): void
    {
        $vertex = $graph->createVertex($name);
        $vertex->setAttribute('name', $name);
        $vertex->setAttribute('places', $places);
    }

    protected function createEdge(Graph $graph, Transition $transition, string $from, string $to): void
    {
        $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
        $edge->setAttribute('name', $transition->getName());

        $transitionMetadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
        $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
        $edge->setWeight($transitionMetadata['weight'] ?? 1);
        $edge->setAttribute('probability', $transitionMetadata['probability'] ?? 1);
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

    protected function createVerticesAndEdgeBetween(
        Graph $graph,
        Transition $transition,
        array $froms,
        array $tos
    ): int {
        $newVertices = 0;
        $from = VertexId::fromPlaces($froms);
        $to = VertexId::fromPlaces($tos);

        if (!$graph->hasVertex($from)) {
            $this->createVertex($graph, $from, $froms);
            ++$newVertices;
        }
        if (!$graph->hasVertex($to)) {
            $this->createVertex($graph, $to, $tos);
            ++$newVertices;
        }
        // TODO: support 2 different transitions but has exactly same froms and tos.
        if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
            $this->createEdge($graph, $transition, $from, $to);
        }

        return $newVertices;
    }

    protected function createVertices(Graph $graph, Transition $transition): int
    {
        $newVertices = 0;
        $froms = $transition->getFroms();
        $tos = $transition->getTos();
        sort($froms);
        sort($tos);

        $newVertices += $this->createVerticesAndEdgeBetween($graph, $transition, $froms, $tos);

        $vertices = $this->findVerticesContains($graph, $froms);
        foreach ($vertices as $vertex) {
            $newFroms = $vertex->getAttribute('places');
            $newTos = array_unique(array_merge(array_diff($newFroms, $froms), $tos));
            sort($newFroms);
            sort($newTos);

            $newVertices += $this->createVerticesAndEdgeBetween($graph, $transition, $newFroms, $newTos);
        }

        return $newVertices;
    }

    protected function findVerticesContains(Graph $graph, array $places): Vertices
    {
        return $graph->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($places) {
            $vertexPlaces = $vertex->getAttribute('places');
            $intersect = array_intersect($vertexPlaces, $places);

            return array_diff($vertexPlaces, $places) && count($intersect) === count($places) && !array_diff($intersect, $places);
        });
    }
}
