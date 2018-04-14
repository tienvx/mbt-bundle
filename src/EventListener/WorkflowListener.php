<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Tienvx\Bundle\MbtBundle\Model\Transition;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class WorkflowListener implements EventSubscriberInterface
{
    public function onAnnounce(Event $event)
    {
        /* @var Subject $subject */
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($subject instanceof Subject && $transition instanceof Transition && (method_exists($subject, $transition->getName()))) {
            call_user_func([$subject, $transition->getName()], $subject->getData());
        }
    }

    public function onEntered(Event $event)
    {
        /* @var Subject $subject */
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        if ($transition instanceof Transition) {
            foreach ($transition->getTos() as $place) {
                if ($subject instanceof Subject && method_exists($subject, $place)) {
                    call_user_func([$subject, $place]);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.announce' => array('onAnnounce', 0),
            'workflow.entered' => array('onEntered'),
        );
    }
}
