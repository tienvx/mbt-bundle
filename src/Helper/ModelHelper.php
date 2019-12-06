<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Symfony\Component\Workflow\Transition;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class ModelHelper
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    public function __construct(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function apply(SubjectInterface $subject, Transition $transition, MarkingStoreInterface $markingStore, Marking $marking, array $context = []): Marking
    {
        $this->leave($transition, $marking);

        $this->transition($subject, $transition, $context);

        $this->enter($transition, $marking);

        $markingStore->setMarking($subject, $marking, $context);

        $this->entered($subject, $marking);

        return $marking;
    }

    protected function leave(Transition $transition, Marking $marking): void
    {
        $places = $transition->getFroms();

        foreach ($places as $place) {
            $marking->unmark($place);
        }
    }

    protected function transition(SubjectInterface $subject, Transition $transition, array $context): void
    {
        $data = $context['data'] ?? null;

        $this->subjectHelper->invokeTransition($subject, $transition->getName(), $data);
    }

    protected function enter(Transition $transition, Marking $marking): void
    {
        $places = $transition->getTos();

        foreach ($places as $place) {
            $marking->mark($place);
        }
    }

    protected function entered(SubjectInterface $subject, Marking $marking): void
    {
        $places = array_keys(array_filter($marking->getPlaces()));
        foreach ($places as $place) {
            $this->subjectHelper->invokePlace($subject, $place);
        }
    }
}
