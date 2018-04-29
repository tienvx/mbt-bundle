<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class WorkflowListener implements EventSubscriberInterface
{
    public function onLeave(Event $event)
    {
        $subject = $event->getSubject();
        if ($subject instanceof Subject && $subject->isRecordingPath()) {
            $subject->recordStep();
        }
    }

    public function onTransition(Event $event)
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($subject instanceof Subject && (method_exists($subject, $transition->getName()))) {
            call_user_func([$subject, $transition->getName()]);
            // reset data for the next transition
            $subject->setData([]);
        }
    }

    public function onEntered(Event $event)
    {
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($subject instanceof Subject) {
            foreach ($transition->getTos() as $place) {
                if (method_exists($subject, $place)) {
                    call_user_func([$subject, $place]);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        // the order of events are: guard -> leave -> transition -> enter -> entered -> completed -> announce (next
        // available transitions)
        return array(
            'workflow.leave' => 'onLeave',
            'workflow.transition' => 'onTransition',
            'workflow.entered' => 'onEntered',
        );
    }
}
