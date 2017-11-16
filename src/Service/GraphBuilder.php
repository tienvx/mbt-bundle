<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Graph;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\Transition;

class GraphBuilder
{
    public function build(Model $model): Graph
    {
        $definition = $model->getDefinition();
        $graph = new Graph();
        foreach ($definition->getPlaces() as $place) {
            $vertex = $graph->createVertex($place);
            $vertex->setAttribute('name', $place);
        }
        /** @var $transition Transition */
        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
                    $edge->setAttribute('name', $transition->getName());
                    $edge->setAttribute('label', $transition->getLabel());
                    // Default weight: 1.
                    $edge->setWeight($transition->getWeight());
                }
            }
        }
        return $graph;
    }
}