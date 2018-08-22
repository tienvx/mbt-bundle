<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class PathRunner
{
    /**
     * @param Path $path
     * @param Workflow $workflow
     * @param Subject $subject
     * @throws \Exception
     */
    public static function run(Path $path, Workflow $workflow, Subject $subject)
    {
        $subject->setUp();

        try {
            foreach ($path->getTransitions() as $transitionName => $data) {
                $subject->setData($data);
                if (!$workflow->can($subject, $transitionName)) {
                    break;
                }
                $workflow->apply($subject, $transitionName);
            }
        } finally {
            $subject->tearDown();
        }
    }
}
