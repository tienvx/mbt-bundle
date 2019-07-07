<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Fhaculty\Graph\Exception as GraphException;
use Generator;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class AllPlacesGenerator extends AbstractGenerator
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
    public function getAvailableTransitions(Workflow $workflow, AbstractSubject $subject, GeneratorOptions $generatorOptions = null): Generator
    {
        $graph = $this->graphBuilder->build($workflow);
        $algorithm = new Bruteforce($graph);
        try {
            $edges = $algorithm->getEdges();
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
        } catch (GraphException $exception) {
        }
    }

    public static function getName(): string
    {
        return 'all-places';
    }

    public function getLabel(): string
    {
        return 'All Places';
    }
}
