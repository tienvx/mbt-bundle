<?php

namespace Tienvx\Bundle\MbtBundle\Helper\Steps;

use Exception;
use Throwable;
use Tienvx\Bundle\MbtBundle\Helper\GuardHelper;
use Tienvx\Bundle\MbtBundle\Helper\SubjectHelper;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Steps\Step;
use Tienvx\Bundle\MbtBundle\Steps\Steps;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;
use Tienvx\Bundle\MbtBundle\Subject\SubjectScreenshotInterface;

class ScreenshotsCapturer
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    /**
     * @var GuardHelper
     */
    protected $guardHelper;

    public function __construct(SubjectHelper $subjectHelper, GuardHelper $guardHelper)
    {
        $this->subjectHelper = $subjectHelper;
        $this->guardHelper = $guardHelper;
    }

    public function capture(Steps $steps, Model $model, SubjectInterface $subject, int $bugId): void
    {
        if (!$subject instanceof SubjectScreenshotInterface) {
            throw new Exception(sprintf('Class %s must implements interface %s', get_class($subject), SubjectScreenshotInterface::class));
        }

        try {
            foreach ($steps as $index => $step) {
                $this->captureStep($step, $index, $model, $subject, $bugId);
            }
        } finally {
            $subject->tearDown();
        }
    }

    protected function captureStep(Step $step, int $index, Model $model, SubjectScreenshotInterface $subject, int $bugId): void
    {
        if ($step->getTransition() && $step->getData() instanceof Data) {
            if (!$this->guardHelper->can($subject, $model->getName(), $step->getTransition())) {
                throw new Exception(sprintf('Transition %s is not enabled', $step->getTransition()));
            }
            try {
                $marking = $model->apply($subject, $step->getTransition());
                $this->subjectHelper->invokeTransition($subject, $step->getTransition(), $step->getData());
                $this->subjectHelper->invokePlaces($subject, array_keys(array_filter($marking->getPlaces())));
            } catch (Throwable $throwable) {
            } finally {
                $subject->captureScreenshot($bugId, $index);
            }
        } elseif (0 === $index) {
            $subject->captureScreenshot($bugId, $index);
        }
    }
}
