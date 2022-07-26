<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;

class EntitySubscriber implements EventSubscriber
{
    protected string $reducer;

    public function __construct(protected MessageBusInterface $messageBus)
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if ($args->getEntity() instanceof Bug) {
            $this->messageBus->dispatch(new ReduceBugMessage($args->getEntity()->getId()));
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }
}
