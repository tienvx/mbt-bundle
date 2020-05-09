<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tienvx\Bundle\MbtBundle\Event\SubjectInitEvent;
use Tienvx\Bundle\MbtBundle\Model\Subject\SetUpInterface;

class SubjectSubscriber implements EventSubscriberInterface
{
    public function onInit(SubjectInitEvent $event): void
    {
        if ($event->getSubject() instanceof SetUpInterface) {
            $event->getSubject()->setUp($event->isTrying());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SubjectInitEvent::class => 'onInit',
        ];
    }
}
