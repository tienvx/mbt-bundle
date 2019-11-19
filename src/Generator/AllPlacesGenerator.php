<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Fhaculty\Graph\Exception as GraphException;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Helper\GraphHelper;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class AllPlacesGenerator extends AbstractGenerator
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
        $algorithm = new Bruteforce($graph);
        try {
            $edges = $algorithm->getEdges();
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
