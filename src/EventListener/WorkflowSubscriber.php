<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class WorkflowSubscriber implements EventSubscriberInterface
{
    public function onEntered(Event $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof Subject) {
            $subject->enterPlace($event->getMarking()->getPlaces());
        }
    }

    public function onTransition(Event $event)
    {
        $subject = $event->getSubject();

        if ($subject instanceof Subject) {
            $subject->applyTransition($event->getTransition()->getName());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.transition' => 'onTransition',
            'workflow.entered' => 'onEntered',
        ];
    }
}
