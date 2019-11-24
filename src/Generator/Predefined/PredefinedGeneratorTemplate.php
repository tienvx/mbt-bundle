<?php

namespace Tienvx\Bundle\MbtBundle\Generator\Predefined;

use Exception;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Graph\VertexId;
use Tienvx\Bundle\MbtBundle\Helper\GraphHelper;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

abstract class PredefinedGeneratorTemplate implements GeneratorInterface
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
    public function generate(Workflow $workflow, SubjectInterface $subject, GeneratorOptions $generatorOptions): iterable
    {
        $graph = $this->graphHelper->build($workflow);
        $initialPlaces = $workflow->getDefinition()->getInitialPlaces();
        $startVertex = $graph->getVertex(VertexId::fromPlaces($initialPlaces));
        $edges = $this->getPredefinedEdges($graph, $startVertex);
        while (count($edges) > 0) {
            $edge = array_shift($edges);
            $transitionName = $edge->getAttribute('name');
            if ($workflow->can($subject, $transitionName)) {
                yield new Step($transitionName, new Data());
            } else {
                break;
            }
        }
    }

    protected function getPredefinedEdges(Graph $graph, Vertex $startVertex): array
    {
        return [];
    }
}
