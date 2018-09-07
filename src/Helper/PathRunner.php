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
            foreach ($path as $index => $step) {
                $transitionName = $step[0];
                $data = $step[1];
                if ($transitionName) {
                    if (is_array($data)) {
                        $subject->setData($data);
                        $subject->setNeedData(false);
                    } else {
                        $subject->setNeedData(true);
                    }
                    if (!$workflow->can($subject, $transitionName)) {
                        break;
                    }
                    $workflow->apply($subject, $transitionName);
                    if (!is_array($data)) {
                        $path->setDataAt($index, $subject->getData());
                    }
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}
