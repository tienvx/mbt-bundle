<?php

namespace Tienvx\Bundle\MbtBundle\Steps;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectScreenshotInterface;

class StepsCapturer
{
    public static function capture(iterable $steps, Workflow $workflow, SubjectInterface $subject, int $bugId): void
    {
        try {
            foreach ($steps as $index => $step) {
                static::captureSingleStep($step, $index, $workflow, $subject, $bugId);
            }
        } finally {
            $subject->tearDown();
        }
    }

    protected static function captureSingleStep(Step $step, int $index, Workflow $workflow, SubjectInterface $subject, int $bugId): void
    {
        if (!$subject instanceof SubjectScreenshotInterface) {
            throw new Exception(sprintf('Class %s must implements interface %s', get_class($subject), SubjectScreenshotInterface::class));
        }

        if ($step->getTransition() && $step->getData() instanceof Data) {
            try {
                $workflow->apply($subject, $step->getTransition(), [
                    'data' => $step->getData(),
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
