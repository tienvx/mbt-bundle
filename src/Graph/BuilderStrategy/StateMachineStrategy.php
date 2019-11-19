<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Exception;
use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;

class StateMachineStrategy extends WorkflowStrategy
{
    /**
     * @throws Exception
     */
    public function build(): Graph
    {
        if (!$this->workflow instanceof StateMachine) {
            throw new Exception('State machine strategy only support state machine');
        }

        $graph = new Graph();
        foreach ($this->workflow->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                $vertexId = VertexId::fromPlaces([$place]);
                $this->createVertex($graph, $vertexId, [$place]);
            }
        }
        foreach ($this->workflow->getDefinition()->getTransitions() as $transition) {
            $this->createEdges($graph, $transition);
        }

        return $graph;
    }

    protected function createEdges(Graph $graph, Transition $transition): void
    {
        foreach ($transition->getFroms() as $from) {
            foreach ($transition->getTos() as $to) {
                $this->createEdge($graph, $transition, VertexId::fromPlaces([$from]), VertexId::fromPlaces([$to]));
            }
        }
    }
}
