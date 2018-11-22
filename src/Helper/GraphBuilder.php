<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
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
        if ($workflow instanceof StateMachine) {
            return self::buildForStateMachine($workflow);
        }

        return self::buildForWorkflow($workflow);
    }

    private static function buildForStateMachine(StateMachine $stateMachine)
    {
        $graph = new Graph();
        foreach ($stateMachine->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                self::createVertex($graph, json_encode([$place]));
                $graph->getVertex(json_encode([$place]))->setAttribute('places', [$place]);
            }
        }
        foreach ($stateMachine->getDefinition()->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    self::createEdge($stateMachine, $graph, $transition, json_encode([$from]), json_encode([$to]));
                }
            }
        }
        return $graph;
    }

    private static function buildForWorkflow(Workflow $workflow)
    {
        $graph = new Graph();
        $newVertices = 0;
        while ($newVertices > 0 || count($graph->getVertices()) === 0) {
            $newVertices = 0;
            foreach ($workflow->getDefinition()->getTransitions() as $transition) {
                $froms = $transition->getFroms();
                $tos = $transition->getTos();
                sort($froms);
                sort($tos);
                // TODO: Clean up vertices and edges that never appear in the path.
                {
                    $from = json_encode($froms);
                    $to = json_encode($tos);
                    if (!$graph->hasVertex($from)) {
                        self::createVertex($graph, $from);
                        $graph->getVertex($from)->setAttribute('places', $froms);
                        $newVertices++;
                    }
                    if (!$graph->hasVertex($to)) {
                        self::createVertex($graph, $to);
                        $graph->getVertex($to)->setAttribute('places', $tos);
                        $newVertices++;
                    }
                    // TODO: support 2 different transitions but has exactly same froms and tos.
                    if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                        self::createEdge($workflow, $graph, $transition, $from, $to);
                    }
                }
                {
                    $vertices = $graph->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($froms) {
                        $places = $vertex->getAttribute('places');
                        return array_diff($places, $froms) && array_intersect($places, $froms) && !array_diff(array_intersect($places, $froms), $froms);
                    });
                    foreach ($vertices as $vertex) {
                        $places = $vertex->getAttribute('places');
                        $newPlaces = array_replace($places, $froms, $tos);
                        sort($places);
                        sort($newPlaces);
                        $from = json_encode($places);
                        $to = json_encode($newPlaces);
                        if (!$graph->hasVertex($to)) {
                            self::createVertex($graph, $to);
                            $graph->getVertex($to)->setAttribute('places', $newPlaces);
                            $newVertices++;
                        }
                        if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                            self::createEdge($workflow, $graph, $transition, $from, $to);
                        }
                    }
                }
            }
        }
        return $graph;
    }

    private static function createVertex(Graph $graph, $name)
    {
        $vertex = $graph->createVertex($name);
        $vertex->setAttribute('name', $name);
    }

    private static function createEdge(Workflow $workflow, Graph $graph, Transition $transition, string $from, string $to)
    {
        $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
        $edge->setAttribute('name', $transition->getName());
        $transitionMetadata = $workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
        $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
        $edge->setWeight(1);
        $edge->setAttribute('weight', $transitionMetadata['weight'] ?? 1);
    }
}
