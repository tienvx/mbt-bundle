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

class Recorder
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

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function record(iterable $steps, Model $model, SubjectInterface $subject, Steps $recorded): void
    {
        $recorded->addStep(new Step(null, new Data(), $model->getDefinition()->getInitialPlaces()));

        foreach ($steps as $step) {
            if ($step instanceof Step && $step->getTransition() && $step->getData() instanceof Data) {
                $this->recordStep($step, $model, $subject, $recorded);
            }
        }
    }

    protected function recordStep(Step $step, Model $model, SubjectInterface $subject, Steps $recorded): void
    {
        if (!$this->guardHelper->can($subject, $model->getName(), $step->getTransition())) {
            throw new Exception(sprintf('Transition %s is not enabled', $step->getTransition()));
        }
        try {
            $marking = $model->apply($subject, $step->getTransition());
            $this->subjectHelper->invokeTransition($subject, $step->getTransition(), $step->getData());
            $this->subjectHelper->invokePlaces($subject, array_keys(array_filter($marking->getPlaces())));
        } catch (Throwable $throwable) {
            throw $throwable;
        } finally {
            $places = array_keys(array_filter($model->getMarking($subject)->getPlaces()));
            $step->setPlaces($places);
            $recorded->addStep($step);
        }
    }
}
