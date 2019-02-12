<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\BugMessage;
use Tienvx\Bundle\MbtBundle\Message\TaskMessage;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(MessageBusInterface $messageBus, CommandRunner $commandRunner)
    {
        $this->messageBus = $messageBus;
        $this->commandRunner = $commandRunner;
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
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Bug) {
            $this->commandRunner->run(['mbt:bug:remove-screenshots', $entity->getId()]);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::preRemove,
        ];
    }
}
