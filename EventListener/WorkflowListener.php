<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Transition;

class WorkflowListener implements EventSubscriberInterface
{
    public function onAnnounce(Event $event)
    {
        $transition = $event->getTransition();
        if ($transition instanceof Transition && (method_exists($event->getSubject(), $transition->getName()))) {
            call_user_func([$event->getSubject(), $transition->getName()], $transition->getData());
        }
    }

    public function onEnterd(Event $event)
    {
        $transition = $event->getTransition();
        if ($transition instanceof Transition) {
            foreach ($transition->getTos() as $place) {
                if (method_exists($event->getSubject(), $place)) {
                    call_user_func([$event->getSubject(), $place]);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.announce' => array('onAnnounce', 0),
            'workflow.entered' => array('onEnterd'),
        );
    }
}
