<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;

class AllPlacesGenerator extends AbstractGenerator
{
    /**
     * @var array
     */
    protected $edges = [];

    public function init(string $model, string $subject, array $arguments, bool $callSUT = false)
    {
        parent::init($model, $subject, $arguments, $callSUT);

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

    public static function getName()
    {
        return 'all-places';
    }
}
