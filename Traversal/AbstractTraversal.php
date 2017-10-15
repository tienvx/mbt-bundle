<?php

namespace Tienvx\Bundle\MbtBundle\Traversal;

use Fhaculty\Graph\Graph;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Workflow\Workflow;

abstract class AbstractTraversal
{
    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var Graph
     */
    protected $graph;

    public function setWorkflow(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function setProgress(ProgressBar $progress)
    {
        $this->progress = $progress;
    }

    public function run(): array
    {
        return $this->getResults();
    }

    protected function getResults(): array
    {
        return [];
    }

    protected function buildGraph(): Graph
    {
        $definition = $this->workflow->getDefinition();
        $graph = new Graph();
        foreach ($definition->getPlaces() as $place) {
            $graph->createVertex($place);
        }
        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('id', "{$transition->getName()}[{$from}=>{$to}]");
                    $weight = null;
                    $edge->setWeight($weight);
                }
            }
        }
        return $graph;
    }
}
