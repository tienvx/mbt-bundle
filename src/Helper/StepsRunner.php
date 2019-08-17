<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Subject\AbstractSubject;

class StepsRunner
{
    /**
     * @param Steps           $steps
     * @param Workflow        $workflow
     * @param AbstractSubject $subject
     *
     * @throws Exception
     */
    public static function run(Steps $steps, Workflow $workflow, AbstractSubject $subject)
    {
        $subject->setUp();

        try {
            foreach ($steps as $step) {
                if ($step->getTransition() && $step->getData() instanceof Data) {
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
