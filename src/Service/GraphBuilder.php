<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Exception;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\ConnectedComponents;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Helper\VertexHelper;

class GraphBuilder
{
    /**
     * @var AdapterInterface
     */
    protected $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Workflow $workflow
     *
     * @return Graph
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function build(Workflow $workflow): Graph
    {
        $cacheItem = $this->cache->getItem('mbt.graph.'.$workflow->getName());
        if (!$cacheItem->isHit()) {
            if ($workflow instanceof StateMachine) {
                $graph = $this->buildForStateMachine($workflow);
            } else {
                $graph = $this->buildForWorkflow($workflow);
                $initVertex = VertexHelper::getId([$workflow->getDefinition()->getInitialPlace()]);
                $components = new ConnectedComponents($graph);
                $graph = $components->createGraphComponentVertex($graph->getVertex($initVertex));
            }

            $cacheItem->set($graph);
            $this->cache->save($cacheItem);
        } else {
            $graph = $cacheItem->get();
        }

        return $graph;
    }

    private function buildForStateMachine(StateMachine $stateMachine)
    {
        $graph = new Graph();
        foreach ($stateMachine->getDefinition()->getPlaces() as $place => $status) {
            if ($status) {
                $this->createVertex($graph, VertexHelper::getId([$place]));
                $graph->getVertex(VertexHelper::getId([$place]))->setAttribute('places', [$place]);
            }
        }
        foreach ($stateMachine->getDefinition()->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                foreach ($transition->getTos() as $to) {
                    $this->createEdge($stateMachine, $graph, $transition, VertexHelper::getId([$from]), VertexHelper::getId([$to]));
                }
            }
        }

        return $graph;
    }

    private function buildForWorkflow(Workflow $workflow)
    {
        $graph = new Graph();
        $newVertices = 0;
        while ($newVertices > 0 || 0 === count($graph->getVertices())) {
            $newVertices = 0;
            foreach ($workflow->getDefinition()->getTransitions() as $transition) {
                $froms = $transition->getFroms();
                $tos = $transition->getTos();
                sort($froms);
                sort($tos);

                // TODO: Clean up vertices and edges that never appear in the path.
                $from = VertexHelper::getId($froms);
                $to = VertexHelper::getId($tos);
                if (!$graph->hasVertex($from)) {
                    $this->createVertex($graph, $from);
                    $graph->getVertex($from)->setAttribute('places', $froms);
                    ++$newVertices;
                }
                if (!$graph->hasVertex($to)) {
                    $this->createVertex($graph, $to);
                    $graph->getVertex($to)->setAttribute('places', $tos);
                    ++$newVertices;
                }
                // TODO: support 2 different transitions but has exactly same froms and tos.
                if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                    $this->createEdge($workflow, $graph, $transition, $from, $to);
                }

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
                    $from = VertexHelper::getId($places);
                    $to = VertexHelper::getId($newPlaces);
                    if (!$graph->hasVertex($to)) {
                        $this->createVertex($graph, $to);
                        $graph->getVertex($to)->setAttribute('places', $newPlaces);
                        ++$newVertices;
                    }
                    if (!$graph->getVertex($from)->hasEdgeTo($graph->getVertex($to))) {
                        $this->createEdge($workflow, $graph, $transition, $from, $to);
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
        $edge->setWeight($transitionMetadata['weight'] ?? 1);
        $edge->setAttribute('probability', $transitionMetadata['probability'] ?? 1);
    }
}
