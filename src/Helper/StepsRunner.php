<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Step;
use Tienvx\Bundle\MbtBundle\Entity\Steps;
use Tienvx\Bundle\MbtBundle\Entity\Data;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class StepsRunner
{
    /**
     * @param iterable         $steps
     * @param Workflow         $workflow
     * @param SubjectInterface $subject
     *
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

    /**
     * @param iterable         $steps
     * @param Workflow         $workflow
     * @param SubjectInterface $subject
     * @param Steps            $recorded
     *
     * @throws Exception
     * @throws Throwable
     */
    public static function record(iterable $steps, Workflow $workflow, SubjectInterface $subject, Steps $recorded)
    {
        $recorded->addStep(new Step(null, new Data(), $workflow->getDefinition()->getInitialPlaces()));

        foreach ($steps as $step) {
            if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof Data) {
                try {
                    $workflow->apply($subject, $step->getTransition(), [
                        'data' => $step->getData(),
                    ]);
                } catch (Throwable $throwable) {
                    throw $throwable;
                } finally {
                    $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
                    $step->setPlaces($places);
                    $recorded->addStep($step);
                }
            }
        }
    }
}
