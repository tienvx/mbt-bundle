<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Messenger\Message\BugMessage;
use Tienvx\Bundle\MbtBundle\Messenger\Message\ReproducePathMessage;
use Tienvx\Bundle\MbtBundle\Messenger\Message\TaskMessage;

class EntitySubscriber implements EventSubscriber
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
        if ($entity instanceof Bug) {
            $this->messageBus->dispatch(new BugMessage($entity->getId()));
        }
        if ($entity instanceof ReproducePath) {
            $this->messageBus->dispatch(new ReproducePathMessage($entity->getId()));
        }
    }

    public function getSubscribedEvents()
    {
        return [
            'postPersist',
        ];
    }
}
