<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowListener implements EventSubscriberInterface
{
    public function onTransition(Event $event)
    {
        if (method_exists($event->getSubject(), $event->getTransition()->getName())) {
            call_user_func([$event->getSubject(), $event->getTransition()->getName()]);
        }
    }

    public function onEnter(Event $event)
    {
        foreach ($event->getTransition()->getTos() as $place) {
            if (method_exists($event->getSubject(), $place)) {
                call_user_func([$event->getSubject(), $place]);
            }
        }
    }

    public function onLeave(Event $event)
    {
        foreach ($event->getTransition()->getTos() as $place) {
            if (method_exists($event->getSubject(), "{$place}OnLeave")) {
                call_user_func([$event->getSubject(), "{$place}OnLeave"]);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.leave' => array('onLeave'),
            'workflow.transition' => array('onTransition'),
            'workflow.enter' => array('onEnter'),
        );
    }
}
