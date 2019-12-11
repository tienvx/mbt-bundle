<?php

namespace Tienvx\Bundle\MbtBundle\Helper\Steps;

use Tienvx\Bundle\MbtBundle\Helper\GuardHelper;
use Tienvx\Bundle\MbtBundle\Helper\SubjectHelper;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Steps\Data;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class Runner
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

    public function run(iterable $steps, Model $model, SubjectInterface $subject): void
    {
        try {
            foreach ($steps as $step) {
                if ($step->getTransition() && $step->getData() instanceof Data && $this->guardHelper->can($subject, $model->getName(), $step->getTransition())) {
                    $marking = $model->apply($subject, $step->getTransition());
                    $this->subjectHelper->invokeTransition($subject, $step->getTransition(), $step->getData());
                    $this->subjectHelper->invokePlaces($subject, array_keys(array_filter($marking->getPlaces())));
                }
            }
        } finally {
            $subject->tearDown();
        }
    }
}
