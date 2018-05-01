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

        foreach ($path->getEdges() as $index => $edge) {
            $transitionName = $edge->getAttribute('name');
            $subject->setData($path->getDataAt($index));
            $subject->setAnnouncing(false);
            if ($workflow->can($subject, $transitionName)) {
                $workflow->apply($subject, $transitionName);
            }
            else {
                break;
            }
        }
    }
}
