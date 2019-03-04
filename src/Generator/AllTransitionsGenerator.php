<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Generator;
use Graphp\Algorithms\ConnectedComponents;
use Psr\SimpleCache\CacheException;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Algorithm\Eulerian;
use Tienvx\Bundle\MbtBundle\Helper\VertexHelper;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class AllTransitionsGenerator extends AbstractGenerator
{
    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(GraphBuilder $graphBuilder)
    {
        $this->graphBuilder = $graphBuilder;
    }

    /**
     * @param Workflow $workflow
     * @param AbstractSubject $subject
     * @return Generator
     * @throws Exception
     * @throws CacheException
     */
    public function getAvailableTransitions(Workflow $workflow, AbstractSubject $subject): Generator
    {
        if (!$workflow instanceof StateMachine) {
            throw new Exception(sprintf('Generator %s only support model type state machine', static::getName()));
        }

        $graph = $this->graphBuilder->build($workflow);
        $components = new ConnectedComponents($graph);
        $singleComponent = $components->isSingle();
        if ($singleComponent) {
            $algorithm = new Eulerian($graph);
            $startVertex = $graph->getVertex(VertexHelper::getId([$workflow->getDefinition()->getInitialPlace()]));
            $edges = $algorithm->getEdges($startVertex);
            $edges = $edges->getVector();
            while (!empty($edges)) {
                $edge = array_shift($edges);
                $transitionName = $edge->getAttribute('name');
                if ($workflow->can($subject, $transitionName)) {
                    yield $transitionName;
                } else {
                    break;
                }
            }
        }
    }

    public static function getName()
    {
        return 'all-transitions';
    }
}
