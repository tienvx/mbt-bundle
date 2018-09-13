<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Generator;
use Graphp\Algorithms\ConnectedComponents;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Algorithm\Eulerian;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class AllTransitionsGenerator extends AbstractGenerator
{
    /**
     * @param Workflow $workflow
     * @param Subject $subject
     * @return Generator
     * @throws Exception
     */
    public function getAvailableTransitions(Workflow $workflow, Subject $subject): Generator
    {
        if (!$workflow instanceof StateMachine) {
            throw new Exception(sprintf('Generator %s only support model type state machine', static::getName()));
        }

        $graph = GraphBuilder::build($workflow);
        $components = new ConnectedComponents($graph);
        $singleComponent = $components->isSingle();
        if ($singleComponent) {
            $algorithm = new Eulerian($graph);
            $startVertex = $graph->getVertex($workflow->getDefinition()->getInitialPlace());
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
