<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Exception;
use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\GraphAttributes;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;

class StateMachineStrategy implements StrategyInterface
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
        if (!$this->workflow instanceof StateMachine) {
            throw new Exception('State machine strategy only support state machine');
        }

        $graph = new Graph();

        $this->createVertices($graph);
        $this->createEdges($graph);

        return $graph;
    }

    protected function createVertices(Graph $graph): void
    {
        $places = array_filter($this->workflow->getDefinition()->getPlaces(), static function ($status) {
            return $status;
        });
        foreach (array_keys($places) as $place) {
            $vertexId = VertexId::fromPlaces([$place]);
            GraphAttributes::createVertex($graph, $vertexId);
        }
    }

    protected function createEdges(Graph $graph): void
    {
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            $metadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
            GraphAttributes::createEdge($graph, VertexId::fromPlaces($transition->getFroms()), VertexId::fromPlaces($transition->getTos()), $transition->getName(), $metadata['label'] ?? '', $metadata['weight'] ?? 1, $metadata['probability'] ?? 1);
        }
    }
}
