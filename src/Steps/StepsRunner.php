<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class StepsRunner
{
    public static function run(iterable $steps, Workflow $workflow, SubjectInterface $subject): void
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
