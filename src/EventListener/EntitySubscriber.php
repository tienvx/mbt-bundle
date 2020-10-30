<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;

class EntitySubscriber implements EventSubscriber
{
    protected MessageBusInterface $messageBus;

    protected string $reducer;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof Task) {
            $this->messageBus->dispatch(new ExecuteTaskMessage($entity->getId()));
        }
        if ($entity instanceof Bug) {
            $this->messageBus->dispatch(new ReduceBugMessage($entity->getId()));
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }
}
