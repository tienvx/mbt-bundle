<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class StepsRecorder
{
    /**
     * @throws Exception
     * @throws Throwable
     */
    public static function record(iterable $steps, Workflow $workflow, SubjectInterface $subject, Steps $recorded): void
    {
        $recorded->addStep(new Step(null, new Data(), $workflow->getDefinition()->getInitialPlaces()));

        foreach ($steps as $step) {
            if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof Data) {
                static::recordStep($step, $workflow, $subject, $recorded);
            }
        }
    }

    protected static function recordStep(Step $step, Workflow $workflow, SubjectInterface $subject, Steps $recorded): void
    {
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
