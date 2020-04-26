<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ApplyBugTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\ApplyTaskTransitionMessage;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\RemoveScreenshotsMessage;
use Tienvx\Bundle\MbtBundle\Reducer\Transition\TransitionReducer;
use Tienvx\Bundle\MbtBundle\Workflow\BugWorkflow;
use Tienvx\Bundle\MbtBundle\Workflow\TaskWorkflow;

class EntitySubscriber implements EventSubscriber
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Task) {
            $this->messageBus->dispatch(new ApplyTaskTransitionMessage($entity->getId(), TaskWorkflow::START));
            $this->messageBus->dispatch(new ExecuteTaskMessage($entity->getId()));
        }
        if ($entity instanceof Bug) {
            $this->messageBus->dispatch(new ApplyBugTransitionMessage($entity->getId(), BugWorkflow::REDUCE));
            $task = $entity->getTask();
            if ($task instanceof Task) {
                $this->messageBus->dispatch(new ReduceBugMessage($entity->getId(), $task->getReducer()->getName()));
            } else {
                $this->messageBus->dispatch(new ReduceBugMessage($entity->getId(), TransitionReducer::getName()));
            }
        }
    }

    /**
     * @throws Exception
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Bug) {
            $this->messageBus->dispatch(new RemoveScreenshotsMessage($entity->getId(), $entity->getWorkflow()->getName()));
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
