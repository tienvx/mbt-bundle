<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Tienvx\Bundle\MbtBundle\Graph\Path;
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

    public function run(Path $path, Model $model)
    {
        $subjectClass = $model->getSubject();
        /* @var Subject $subject */
        $subject = new $subjectClass();
        $subject->setCallSUT(true);

        foreach ($path as $position => $step) {
            if ($step instanceof Vertex) {
                $place = $step->getAttribute('name');
                $marking = $model->getMarking($subject);
                if (!$marking->has($place)) {
                    break;
                }
            }
            else if ($step instanceof Directed) {
                $transition = $step->getAttribute('name');
                if (!$path->hasDataAtPosition($position)) {
                    $data = $this->dataProvider->getData($subject, $model->getName(), $transition);
                    $path->setDataAtPosition($position, $data);
                }
                else {
                    $data = $path->getDataAtPosition($position);
                }
                $subject->setData($data);
                if ($model->can($subject, $transition)) {
                    $model->apply($subject, $transition);
                }
                else {
                    break;
                }
            }
        }
    }
}
