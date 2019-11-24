<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Tienvx\Bundle\MbtBundle\Helper\SubjectHelper;
use Tienvx\Bundle\MbtBundle\Subject\SubjectInterface;

class WorkflowSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    public function __construct(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function onEntered(EnteredEvent $event): void
    {
        $subject = $event->getSubject();

        if ($subject instanceof SubjectInterface) {
            $places = array_keys(array_filter($event->getMarking()->getPlaces()));
            foreach ($places as $place) {
                $this->subjectHelper->invokePlace($subject, $place);
            }
        }
    }

    public function onTransition(TransitionEvent $event): void
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition()->getName();
        $data = $event->getContext()['data'] ?? null;

        if ($subject instanceof SubjectInterface) {
            $this->subjectHelper->invokeTransition($subject, $transition, $data);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            TransitionEvent::class => 'onTransition',
            EnteredEvent::class => 'onEntered',
        ];
    }
}
