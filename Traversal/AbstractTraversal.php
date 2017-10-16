<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Workflow\Workflow;

abstract class AbstractTraversal
{
    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @var Vertex
     */
    protected $currentVertex;

    public function setWorkflow(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function getNextStep(): array
    {
        return [];
    }

    public function hasNextStep(): bool
    {
        return false;
    }

    public function getMaxProgress(): int
    {
        return 0;
    }

    public function getCurrentProgress(): int
    {
        return 0;
    }

    public function getCurrentProgressMessage(): string
    {
        return '';
    }

    public function init()
    {
        $this->graph = $this->buildGraph();
        $this->currentVertex = $this->graph->getVertex($this->workflow->getDefinition()->getInitialPlace());
    }

    protected function buildGraph(): Graph
    {
        $definition = $this->workflow->getDefinition();
        $graph = new Graph();
        foreach ($definition->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
            $vertex->setAttribute('key', $place);
            $vertex->setAttribute('text', "place:$place");
        }
        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $edge->setAttribute('key', "{$transition->getName()}:$from:$to");
                    $edge->setAttribute('text', "transition:{$transition->getName()}[$from=>$to]");
                    $weight = null;
                    $edge->setWeight($weight);
                }
            }
        }
        return $graph;
    }
}
