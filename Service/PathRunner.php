<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Walk;
use Tienvx\Bundle\MbtBundle\Exception\ModelInWrongPlaceException;
use Tienvx\Bundle\MbtBundle\Exception\TransitionCanNotBeAppliedException;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class PathRunner
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function run(Walk $walk, Model $model)
    {
        $subjectClass = $model->getSubject();
        /* @var $subject Subject */
        $subject = new $subjectClass();
        $subject->setCallSUT(true);

        $steps = $walk->getAlternatingSequence();
        foreach ($steps as $step) {
            $marking = $model->getMarking($subject);
            if ($step instanceof Vertex) {
                $place = $step->getAttribute('name');
                if (!$marking->has($place)) {
                    throw new ModelInWrongPlaceException(sprintf('Expected current place to be "%s", but got "%s"', $place, $marking->getPlaces()[0]));
                }
            }
            else if ($step instanceof Directed) {
                $transition = $step->getAttribute('name');
                $data = $step->getAttribute('data');
                if ($model->can($subject, $transition)) {
                    $subject->setData($data);
                    $model->apply($subject, $transition);
                }
                else {
                    throw new TransitionCanNotBeAppliedException(sprintf('Can not apply transition "%s" with data "%s"', $transition, json_encode($data)));
                }
            }
        }
    }

    public function canWalk(Walk $walk, Model $model)
    {
        $subjectClass = $model->getSubject();
        /* @var $subject Subject */
        $subject = new $subjectClass();

        $steps = $walk->getAlternatingSequence();
        foreach ($steps as $step) {
            $marking = $model->getMarking($subject);
            if ($step instanceof Vertex) {
                $place = $step->getAttribute('name');
                if (!$marking->has($place)) {
                    return false;
                }
            }
            else if ($step instanceof Directed) {
                $transition = $step->getAttribute('name');
                if ($model->can($subject, $transition)) {
                    $data = $this->dataProvider->getData($subject, $model->getName(), $transition);
                    $subject->setData($data);
                    $step->setAttribute('data', $data);
                    $model->apply($subject, $transition);
                }
                else {
                    return false;
                }
            }
        }
        return true;
    }
}
