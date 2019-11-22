<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class GraphWithAttributes
{
    /**
     * @var Graph
     */
    protected $graph;

    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct(Graph $graph, Workflow $workflow)
    {
        $this->graph = $graph;
        $this->workflow = $workflow;
    }

    public function createVertex(string $name, array $places): void
    {
        $vertex = $this->graph->createVertex($name);
        $vertex->setAttribute('name', $name);
        $vertex->setAttribute('places', $places);
    }

    public function createEdge(Transition $transition, string $from, string $to): void
    {
        $edge = $this->graph->getVertex($from)->createEdgeTo($this->graph->getVertex($to));
        $edge->setAttribute('name', $transition->getName());

        $transitionMetadata = $this->workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
        $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
        $edge->setWeight($transitionMetadata['weight'] ?? 1);
        $edge->setAttribute('probability', $transitionMetadata['probability'] ?? 1);
    }
}
