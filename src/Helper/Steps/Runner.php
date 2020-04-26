<?php

namespace Tienvx\Bundle\MbtBundle\Helper\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\EventListener\WorkflowSubscriber;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;

class Runner
{
    public function run(iterable $steps, Workflow $workflow, object $subject): void
    {
        foreach ($steps as $step) {
            if ($step->getTransition() && $step->getData() instanceof Data) {
                $this->runStep($step, $workflow, $subject, $step->getData());
            }
        }
    }

    protected function runStep(Step $step, Workflow $workflow, object $subject, Data $data): void
    {
        if ($workflow->can($subject, $step->getTransition())) {
            $workflow->apply($subject, $step->getTransition(), [
                Workflow::DISABLE_ANNOUNCE_EVENT => true,
                WorkflowSubscriber::DATA_CONTEXT => $data,
            ]);
        } else {
            throw new Exception(sprintf('Transition %s is not enabled', $step->getTransition()));
        }
    }
}
