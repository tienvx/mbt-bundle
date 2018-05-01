<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class WorkflowSubscriber implements EventSubscriberInterface
{
    public function onTransition(Event $event)
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($subject instanceof Subject) {
            $subject('transition', $transition->getName());
        }
    }

    public function onEnter(Event $event)
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($subject instanceof Subject) {
            foreach ($transition->getTos() as $place) {
                $subject('place', $place);
            }
        }
    }

    public function onCompleted(Event $event)
    {
        $subject = $event->getSubject();
        if ($subject instanceof Subject) {
            $subject->setAnnouncing(true);
        }
    }

    public static function getSubscribedEvents()
    {
        // the order of events are: guard -> leave -> transition -> enter -> entered -> completed -> announce (next
        // available transitions)
        return [
            'workflow.transition' => 'onTransition',
            'workflow.enter' => 'onEnter',
            'workflow.completed' => 'onCompleted',
        ];
    }
}
