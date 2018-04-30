<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class TaskSubscriber implements EventSubscriberInterface
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Task) {
            $this->messageBus->dispatch(new TaskMessage($entity->getId()));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'postPersist',
        ];
    }
}
