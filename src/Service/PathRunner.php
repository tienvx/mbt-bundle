<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Vertex;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class PathRunner
{
    /**
     * @var Registry
     */
    protected $workflows;

    public function __construct(Registry $workflows)
    {
        $this->workflows = $workflows;
    }

    public function run(Path $path, string $model, string $subject)
    {
        /* @var Subject $subject */
        $subject = new $subject();
        $subject->setCallSUT(true);
        $workflow = $this->workflows->get($subject, $model);

        foreach ($path as $position => $step) {
            if ($step instanceof Vertex) {
                $place = $step->getAttribute('name');
                $marking = $workflow->getMarking($subject);
                if (!$marking->has($place)) {
                    break;
                }
            }
            else if ($step instanceof Directed) {
                $transition = $step->getAttribute('name');
                if ($path->hasDataAtPosition($position)) {
                    $data = $path->getDataAtPosition($position);
                }
                else {
                    $data = null;
                }
                $subject->setData($data);
                if ($workflow->can($subject, $transition)) {
                    $workflow->apply($subject, $transition);
                    $path->setDataAtPosition($position, $subject->getData());
                }
                else {
                    break;
                }
            }
        }
    }
}
