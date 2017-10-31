<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\Transition;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

abstract class AbstractTraversal
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

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

    /**
     * @var Directed
     */
    protected $currentEdge;

    /**
     * @var array
     */
    protected $testSequence;

    /**
     * @var Subject
     */
    protected $subject;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function setArgs($args)
    {
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function getTestSequence(): array
    {
        return $this->testSequence;
    }

    public function hasNextStep(): bool
    {
        return false;
    }

    public function canGoNextStep(): bool
    {
        return false;
    }

    public function goToNextStep(bool $callSUT = false)
    {
    }

    public function getMaxProgress(): int
    {
        return 100;
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

        $this->testSequence = [];
        $this->testSequence[] = $this->currentVertex->getAttribute('name');

        $subjectClass = $this->model->getSubject();
        $this->subject = new $subjectClass();
    }

    protected function buildGraph(): Graph
    {
        $definition = $this->model->getDefinition();
        $graph = new Graph();
        foreach ($definition->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
        }
        /** @var Transition $transition */
        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $edge->setWeight($transition->getWeight());
                }
            }
        }
        return $graph;
    }
}
