<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Symfony\Component\Workflow\Marking;
use Throwable;
use Tienvx\Bundle\MbtBundle\Model\Model;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class MarkingHelper
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    public function __construct(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function init(Model $model, SubjectInterface $subject): void
    {
        $marking = $model->getMarkingStore()->getMarking($subject);
        if (!$marking->getPlaces()) {
            $this->initPlaces($model, $subject, $marking);
        }
        $this->validatePlaces($model, $marking);
    }

    protected function initPlaces(Model $model, SubjectInterface $subject, Marking $marking): void
    {
        if (!$model->getDefinition()->getInitialPlaces()) {
            throw new LogicException(sprintf('The Marking is empty and there is no initial place for workflow "%s".', $this->name));
        }
        foreach ($model->getDefinition()->getInitialPlaces() as $place) {
            $marking->mark($place);
        }

        // update the subject with the new marking
        $model->getMarkingStore()->setMarking($subject, $marking);

        $this->subjectHelper->invokePlaces($subject, array_keys(array_filter($marking->getPlaces())));
    }

    protected function validatePlaces(Model $model, Marking $marking): void
    {
        $places = $model->getDefinition()->getPlaces();
        foreach ($marking->getPlaces() as $placeName => $nbToken) {
            if (isset($places[$placeName])) {
                continue;
            }
            $message = sprintf('Place "%s" is not valid for workflow "%s".', $placeName, $model->getName());
            if (!$places) {
                $message .= ' It seems you forgot to add places to the current workflow.';
            }

            throw new LogicException($message);
        }
    }
}
