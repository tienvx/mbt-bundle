<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Exception;
use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\GraphWithAttributes;
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
        $graphWithAttributes = new GraphWithAttributes($graph, $this->workflow);

        $this->createVertices($graphWithAttributes);
        $this->createEdges($graphWithAttributes);

        return $graph;
    }

    protected function createVertices(GraphWithAttributes $graphWithAttributes): void
    {
        $places = array_filter($this->workflow->getDefinition()->getPlaces(), static function ($status) {
            return $status;
        });
        foreach (array_keys($places) as $place) {
            $vertexId = VertexId::fromPlaces([$place]);
            $graphWithAttributes->createVertex($vertexId, [$place]);
        }
    }

    protected function createEdges(GraphWithAttributes $graphWithAttributes): void
    {
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            $graphWithAttributes->createEdge($transition, VertexId::fromPlaces($transition->getFroms()), VertexId::fromPlaces($transition->getTos()));
        }
    }
}
