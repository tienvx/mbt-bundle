<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Set\Edges;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;
use Tienvx\Bundle\MbtBundle\Annotation\Generator;

/**
 * @Generator(
 *     name = "all-places",
 *     label = "All Places"
 * )
 */
class AllPlacesGenerator extends AbstractGenerator
{
    /**
     * @var array
     */
    protected $edges = [];

    public function init()
    {
        parent::init();

        $algorithm = new Bruteforce($this->graph);
        $edges = $algorithm->getEdges();
        $this->edges = $edges->getVector();
    }

    public function getNextStep(): ?Directed
    {
        return array_shift($this->edges);
    }

    public function meetStopCondition(): bool
    {
        return empty($this->edges);
    }
}
