<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class WorkflowSubscriber implements EventSubscriberInterface
{
    public function onGuard(Event $event)
    {
        $subject = $event->getSubject();
        if ($subject instanceof Subject && $subject->isAnnouncing()) {
            // on annoucing, workflow component check for next available transitions, and we need
            // to disable the checking
            $event->stopPropagation();
        }
    }

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

    public function onAnnounce(Event $event)
    {
        $subject = $event->getSubject();
        if ($subject instanceof Subject) {
            // for models, next available transition will be selected by generator, so triggering annouce events
            // is not necessary
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        // the order of events are: guard -> leave -> transition -> enter -> entered -> completed -> announce (next
        // available transitions)
        return [
            'workflow.guard' => 'onGuard',
            'workflow.transition' => 'onTransition',
            'workflow.enter' => 'onEnter',
            'workflow.completed' => 'onCompleted',
            'workflow.announce' => 'onAnnounce',
        ];
    }
}
