<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Fhaculty\Graph\Edge\Directed;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Model\Subject;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;

class AllPlacesGenerator extends AbstractGenerator
{
    /**
     * @var array
     */
    protected $edges = [];

    public function init(Model $model, Subject $subject, StopConditionInterface $stopCondition)
    {
        parent::init($model, $subject, $stopCondition);

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
