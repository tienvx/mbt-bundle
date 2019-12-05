<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\GraphAttributes;
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
        $verticesCount = 0;
        do {
            $verticesCount = $graph->getVertices()->count();
            foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
                $froms = $transition->getFroms();
                $tos = $transition->getTos();
                sort($froms);
                sort($tos);

                if (0 === $verticesCount) {
                    $this->createVerticesAndEdge($graph, $transition, $froms, $tos);
                }
                $this->createRelatedVerticesAndEdges($graph, $transition, $froms, $tos);
            }
        } while ($graph->getVertices()->count() - $verticesCount > 0);

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

    protected function createVerticesAndEdge(Graph $graph, Transition $transition, array $froms, array $tos): void
    {
        $from = VertexId::fromPlaces($froms);
        $to = VertexId::fromPlaces($tos);

        GraphAttributes::createVertex($graph, $from);
        GraphAttributes::createVertex($graph, $to);

        $metadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
        GraphAttributes::createEdge($graph, $from, $to, $transition->getName(), $metadata['label'] ?? '', $metadata['weight'] ?? 1, $metadata['probability'] ?? 1);
    }

    protected function createRelatedVerticesAndEdges(Graph $graph, Transition $transition, array $froms, array $tos): void
    {
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

            $this->createVerticesAndEdge($graph, $transition, $newFroms, $newTos);
        }
    }
}
