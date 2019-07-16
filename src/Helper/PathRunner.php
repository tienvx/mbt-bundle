<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Entity\StepData;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class PathRunner
{
    /**
     * @param Path            $path
     * @param Workflow        $workflow
     * @param AbstractSubject $subject
     *
     * @throws Exception
     */
    public static function run(Path $path, Workflow $workflow, AbstractSubject $subject)
    {
        $subject->setUp();

        try {
            foreach ($path->getSteps() as $index => $step) {
                $transition = $step->getTransition();
                $data = $step->getData();
                if ($transition) {
                    if ($data instanceof StepData) {
                        $subject->setData($data);
                        $subject->setNeedData(false);
                    } else {
                        $subject->setNeedData(true);
                    }
                    if (!$workflow->can($subject, $transition)) {
                        break;
                    }
                    // Store data before apply transition, because there are maybe exception happen
                    // while applying transition.
                    if (!($data instanceof StepData)) {
                        $path->setDataAt($index, $subject->getData());
                    }
                    $subject->setNeedData(false);
                    $workflow->apply($subject, $transition);
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}
