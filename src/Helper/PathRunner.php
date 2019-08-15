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
            foreach ($path->getSteps() as $step) {
                if ($step->getTransition() && $step->getData() instanceof StepData) {
                    $workflow->apply($subject, $step->getTransition(), [
                        'data' => $step->getData(),
                    ]);
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}
