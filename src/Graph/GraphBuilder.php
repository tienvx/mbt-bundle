<?php

namespace Tienvx\Bundle\MbtBundle\Graph;

use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\Definition;

class GraphBuilder
{
    public function build(Definition $workflowDefinition): Graph
    {
        $graph = new Graph();
        foreach ($workflowDefinition->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
        }
        foreach ($workflowDefinition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $transitionMetadata = $workflowDefinition->getMetadataStore()->getTransitionMetadata($transition);
                    $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
                    $edge->setWeight(1);
                    $edge->setAttribute('weight', $transitionMetadata['weight'] ?? 1);
                }
            }
        }
        return $graph;
    }
}