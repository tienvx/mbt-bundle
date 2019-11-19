<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class StepsRunner
{
    /**
     * @throws Exception
     */
    public static function run(iterable $steps, Workflow $workflow, SubjectInterface $subject)
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
