<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

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

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        if ($entity instanceof Model) {
            $updateVersion = false;
            if ($args->hasChangedField('startCommands')) {
                $updateVersion = !$this->isSameStartCommands(
                    $args->getOldValue('startCommands'),
                    $args->getNewValue('startCommands')
                );
            }
            if ($args->hasChangedField('places')) {
                $updateVersion = !$this->isSamePlaces($args->getOldValue('places'), $args->getNewValue('places'));
            }
            if ($args->hasChangedField('transitions')) {
                $updateVersion = !$this->isSameTransitions(
                    $args->getOldValue('transitions'),
                    $args->getNewValue('transitions')
                );
            }
            if ($updateVersion) {
                $entity->setVersion($entity->getVersion() + 1);
            }
        }
    }

    protected function isSameStartCommands($oldCommands, $newCommands): bool
    {
        if (!$this->isArraysSameSize($oldCommands, $newCommands)) {
            return false;
        }
        foreach ($newCommands as $index => $newCommand) {
            $oldCommand = $oldCommands[$index] ?? null;
            if (
                !$oldCommand instanceof CommandInterface ||
                !$newCommand instanceof CommandInterface ||
                !$oldCommand->isSame($newCommand)
            ) {
                return false;
            }
        }

        return true;
    }

    protected function isSamePlaces($oldPlaces, $newPlaces): bool
    {
        if (!$this->isArraysSameSize($oldPlaces, $newPlaces)) {
            return false;
        }
        foreach ($newPlaces as $index => $newPlace) {
            $oldPlace = $oldPlaces[$index] ?? null;
            if (
                !$oldPlace instanceof PlaceInterface ||
                !$newPlace instanceof PlaceInterface ||
                !$oldPlace->isSame($newPlace)
            ) {
                return false;
            }
        }

        return true;
    }

    protected function isSameTransitions($oldTransitions, $newTransitions): bool
    {
        if (!$this->isArraysSameSize($oldTransitions, $newTransitions)) {
            return false;
        }
        foreach ($newTransitions as $index => $newTransition) {
            $oldTransition = $oldTransitions[$index] ?? null;
            if (
                !$oldTransition instanceof TransitionInterface ||
                !$newTransition instanceof TransitionInterface ||
                !$oldTransition->isSame($newTransition)
            ) {
                return false;
            }
        }

        return true;
    }

    protected function isArraysSameSize($old, $new): bool
    {
        return is_array($old) && is_array($new) && count($old) === count($new);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::preUpdate,
        ];
    }
}
