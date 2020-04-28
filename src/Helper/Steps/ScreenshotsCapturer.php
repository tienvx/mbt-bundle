<?php

namespace Tienvx\Bundle\MbtBundle\Helper\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\EventListener\WorkflowSubscriber;
use Tienvx\Bundle\MbtBundle\Model\Subject\ScreenshotInterface;
use Tienvx\Bundle\MbtBundle\Model\Subject\TearDownInterface;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class ScreenshotsCapturer
{
    public function capture(Steps $steps, Workflow $workflow, object $subject, int $bugId): void
    {
        if (!$subject instanceof ScreenshotInterface) {
            throw new Exception(sprintf('Class %s must implements interface %s', get_class($subject), ScreenshotInterface::class));
        }

        try {
            foreach ($steps as $index => $step) {
                $this->captureStep($step, $index, $workflow, $subject, $bugId);
            }
        } finally {
            if ($subject instanceof TearDownInterface) {
                $subject->tearDown();
            }
        }
    }

    protected function captureStep(Step $step, int $index, Workflow $workflow, ScreenshotInterface $subject, int $bugId): void
    {
        if ($step->getTransition() && $step->getData() instanceof Data) {
            if (!$workflow->can($subject, $step->getTransition())) {
                throw new Exception(sprintf('Transition %s is not enabled', $step->getTransition()));
            }
            try {
                $workflow->apply($subject, $step->getTransition(), [
                    Workflow::DISABLE_ANNOUNCE_EVENT => true,
                    WorkflowSubscriber::DATA_CONTEXT => $step->getData(),
                ]);
            } catch (Throwable $throwable) {
            } finally {
                $subject->captureScreenshot($bugId, $index);
            }
        } elseif (0 === $index) {
            $subject->captureScreenshot($bugId, $index);
        }
    }
}
