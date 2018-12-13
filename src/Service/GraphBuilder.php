<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Exception;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\CacheException;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class GraphBuilder
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Workflow $workflow
     * @return Graph
     * @throws Exception
     * @throws CacheException
     */
    public function build(Workflow $workflow): Graph
    {
        if ($this->cache->has('mbt.graph.' . $workflow->getName())) {
            return $this->cache->get('mbt.graph.' . $workflow->getName());
        }
        if ($workflow instanceof StateMachine) {
            $graph = $this->buildForStateMachine($workflow);
        } else {
            $graph = $this->buildForWorkflow($workflow);
        }

        $this->cache->set('mbt.graph.' . $workflow->getName(), $graph);
        return $graph;
    }

    private function buildForStateMachine(StateMachine $stateMachine)
    {
        $graph = new Graph();
        foreach ($stateMachine->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                $this->createVertex($graph, json_encode([$place]));
                $graph->getVertex(json_encode([$place]))->setAttribute('places', [$place]);
            }
        }
        foreach ($stateMachine->getDefinition()->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $this->createEdge($stateMachine, $graph, $transition, json_encode([$from]), json_encode([$to]));
                }
            }
        }
        return $graph;
    }

    private function buildForWorkflow(Workflow $workflow)
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
                        $this->createVertex($graph, $from);
                        $graph->getVertex($from)->setAttribute('places', $froms);
                        $newVertices++;
                    }
                    if (!$graph->hasVertex($to)) {
                        $this->createVertex($graph, $to);
                        $graph->getVertex($to)->setAttribute('places', $tos);
                        $newVertices++;
                    }
                    // TODO: support 2 different transitions but has exactly same froms and tos.
                    if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                        $this->createEdge($workflow, $graph, $transition, $from, $to);
                    }
                }
                {
                    $vertices = $graph->getVertices()->getVerticesMatch(function (Vertex $vertex) use ($froms) {
                        $places = $vertex->getAttribute('places');
                        $intersect = array_intersect($places, $froms);
                        return array_diff($places, $froms) && count($intersect) === count($froms) && !array_diff($intersect, $froms);
                    });
                    foreach ($vertices as $vertex) {
                        $places = $vertex->getAttribute('places');
                        $newPlaces = array_unique(array_merge(array_diff($places, $froms), $tos));
                        sort($places);
                        sort($newPlaces);
                        $from = json_encode($places);
                        $to = json_encode($newPlaces);
                        if (!$graph->hasVertex($to)) {
                            $this->createVertex($graph, $to);
                            $graph->getVertex($to)->setAttribute('places', $newPlaces);
                            $newVertices++;
                        }
                        if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                            $this->createEdge($workflow, $graph, $transition, $from, $to);
                        }
                    }
                }
            }
        }
        return $graph;
    }

    private function createVertex(Graph $graph, $name)
    {
        $vertex = $graph->createVertex($name);
        $vertex->setAttribute('name', $name);
    }

    private function createEdge(Workflow $workflow, Graph $graph, Transition $transition, string $from, string $to)
    {
        $edge = $graph->getVertex($from)->createEdgeTo($graph->getVertex($to));
        $edge->setAttribute('name', $transition->getName());
        $transitionMetadata = $workflow->getDefinition()->getMetadataStore()->getTransitionMetadata($transition);
        $edge->setAttribute('label', $transitionMetadata['label'] ?? '');
        $edge->setWeight(1);
        $edge->setAttribute('weight', $transitionMetadata['weight'] ?? 1);
    }
}
