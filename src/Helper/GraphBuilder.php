<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Graph;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;

class GraphBuilder
{
    /**
     * @param Workflow $workflow
     * @return Graph
     * @throws Exception
     */
    public static function build(Workflow $workflow): Graph
    {
        if (!$workflow instanceof StateMachine) {
            throw new Exception('Can not build graph for model type workflow');
        }

        $graph = new Graph();
        foreach ($workflow->getDefinition()->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
        }
        foreach ($workflow->getDefinition()->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $transitionMetadata = $workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
                    $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
                    $edge->setWeight(1);
                    $edge->setAttribute('weight', $transitionMetadata['weight'] ?? 1);
                }
            }
        }
        return $graph;
    }
}