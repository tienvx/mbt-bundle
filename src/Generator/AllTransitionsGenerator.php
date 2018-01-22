<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Tienvx\Bundle\MbtBundle\Algorithm\Eulerian;
use Tienvx\Bundle\MbtBundle\Annotation\Generator;

/**
 * @Generator(
 *     name = "all-transitions",
 *     label = "All Transitions"
 * )
 */
class AllTransitionsGenerator extends AbstractGenerator
{
    /**
     * @var array
     */
    protected $edges = [];

    public function init()
    {
        parent::init();

        $algorithm = new Eulerian($this->graph);
        $algorithm->setStartVertex($this->currentVertex);
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
