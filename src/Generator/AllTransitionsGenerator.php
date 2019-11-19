<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ConnectedComponents;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Graph\Algorithm\Eulerian;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;
use Tienvx\Bundle\MbtBundle\Helper\GraphHelper;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class AllTransitionsGenerator extends AbstractGenerator
{
    /**
     * @var GraphHelper
     */
    protected $graphHelper;

    public function __construct(GraphHelper $graphHelper)
    {
        $this->graphHelper = $graphHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions = null): iterable
    {
        $graph = $this->graphHelper->build($workflow);
        if ($this->singleComponent($graph)) {
            $algorithm = new Eulerian($graph);
            $startVertex = $graph->getVertex(VertexId::fromPlaces($workflow->getDefinition()->getInitialPlaces()));
            $edges = $algorithm->getEdges($startVertex)->getVector();
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

    protected function singleComponent(Graph $graph): bool
    {
        $components = new ConnectedComponents($graph);

        return $components->isSingle();
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
