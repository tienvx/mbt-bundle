<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Graphp\Algorithms\ConnectedComponents;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Algorithm\Eulerian;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Data;
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
     * {@inheritdoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function generate(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): iterable
    {
        $graph = $this->graphBuilder->build($workflow);
        $components = new ConnectedComponents($graph);
        $singleComponent = $components->isSingle();
        if ($singleComponent) {
            $algorithm = new Eulerian($graph);
            $startVertex = $graph->getVertex(VertexHelper::getId($workflow->getDefinition()->getInitialPlaces()));
            $edges = $algorithm->getEdges($startVertex);
            $edges = $edges->getVector();
            while (!empty($edges)) {
                $edge = array_shift($edges);
                $transitionName = $edge->getAttribute('name');
                if ($workflow->can($subject, $transitionName)) {
                    yield new Step($transitionName, new Data());
                } else {
                    break;
                }
            }
        }
    }

    public static function getName(): string
    {
        return 'all-transitions';
    }

    public function getLabel(): string
    {
        return 'All Transitions';
    }
}
