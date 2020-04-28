<?php

namespace Tienvx\Bundle\MbtBundle\Helper\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\EventListener\WorkflowSubscriber;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class Recorder
{
    /**
     * @throws Exception
     * @throws Throwable
     */
    public function record(iterable $steps, Workflow $workflow, object $subject, Steps $recorded): void
    {
        $recorded->addStep(new Step(null, new Data(), $workflow->getDefinition()->getInitialPlaces()));

        foreach ($steps as $step) {
            if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof Data) {
                $this->recordStep($step, $workflow, $subject, $recorded, $step->getData());
            }
        }
    }

    protected function recordStep(Step $step, Workflow $workflow, object $subject, Steps $recorded, Data $data): void
    {
        try {
            if ($workflow->can($subject, $step->getTransition())) {
                $workflow->apply($subject, $step->getTransition(), [
                    Workflow::DISABLE_ANNOUNCE_EVENT => true,
                    WorkflowSubscriber::DATA_CONTEXT => $data,
                ]);
            }
        } catch (Throwable $throwable) {
            throw $throwable;
        } finally {
            $places = array_keys(array_filter($workflow->getMarking($subject)->getPlaces()));
            $step->setPlaces($places);
            $recorded->addStep($step);
        }
    }
}
