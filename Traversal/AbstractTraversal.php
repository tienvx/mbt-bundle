<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\Transition;

abstract class AbstractTraversal
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @var Vertex
     */
    protected $currentVertex;

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function getCurrentVertex(): Vertex
    {
        return $this->currentVertex;
    }

    public function getNextStep(): Directed
    {
        return null;
    }

    public function hasNextStep(): bool
    {
        return false;
    }

    public function goToNextStep(Directed $edge)
    {
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

    public function meetStopCondition(): bool
    {
        return false;
    }

    public function init()
    {
        $this->graph = $this->buildGraph();
        $this->currentVertex = $this->graph->getVertex($this->model->getDefinition()->getInitialPlace());
    }

    protected function buildGraph(): Graph
    {
        $definition = $this->model->getDefinition();
        $graph = new Graph();
        foreach ($definition->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
            $vertex->setAttribute('key', $place);
            $vertex->setAttribute('text', "place:$place");
        }
            /** @var Transition $transition */
        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $edge->setAttribute('key', "{$transition->getName()}:$from:$to");
                    $edge->setAttribute('text', "transition:{$transition->getName()}[$from=>$to]");
                    $edge->setWeight($transition->getWeight());
                }
            }
        }
        return $graph;
    }
}
