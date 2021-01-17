<?php

namespace Tienvx\Bundle\MbtBundle\Tests\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\TransitionInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber
 * @covers \Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 */
class EntitySubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new EntitySubscriber($this->createMock(MessageBusInterface::class));
        $this->assertSame([
            Events::postPersist,
            Events::preUpdate,
        ], $subscriber->getSubscribedEvents());
    }

    public function testPostPersistTask(): void
    {
        $task = new Task();
        $task->setId(12);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn ($message) => $message instanceof ExecuteTaskMessage && 12 === $message->getId()))
            ->willReturn(new Envelope(new \stdClass()));
        $subscriber = new EntitySubscriber($messageBus);
        $args = new LifecycleEventArgs($task, $this->createMock(ObjectManager::class));
        $subscriber->postPersist($args);
    }

    public function testPostPersistBug(): void
    {
        $bug = new Bug();
        $bug->setId(23);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn ($message) => $message instanceof ReduceBugMessage && 23 === $message->getId()))
            ->willReturn(new Envelope(new \stdClass()));
        $subscriber = new EntitySubscriber($messageBus);
        $args = new LifecycleEventArgs($bug, $this->createMock(ObjectManager::class));
        $subscriber->postPersist($args);
    }

    public function testPostPersistOtherEntity(): void
    {
        $model = new Model();
        $model->setId(34);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->never())->method('dispatch');
        $subscriber = new EntitySubscriber($messageBus);
        $args = new LifecycleEventArgs($model, $this->createMock(ObjectManager::class));
        $subscriber->postPersist($args);
    }

    public function testPreUpdateSamePlacesAndTransitions(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturn(false);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(1, $model->getVersion());
    }

    public function testPreUpdateInvalidPlaces(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', true],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('places')->willReturn('old places');
        $args->expects($this->once())->method('getNewValue')->with('places')->willReturn('new places');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSameNumberOfPlaces(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', true],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('places')->willReturn([
            'place 1',
            'place 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('places')->willReturn([
            'place 1',
            'place 2',
            'place 3',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateInvalidPlace(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', true],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('places')->willReturn([
            'place 1',
            'place 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('places')->willReturn([
            'place 1',
            'place 2',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSamePlace(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', true],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('places')->willReturn([
            $place1 = $this->createMock(PlaceInterface::class),
            $place2 = $this->createMock(PlaceInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('places')->willReturn([
            $place3 = $this->createMock(PlaceInterface::class),
            $place4 = $this->createMock(PlaceInterface::class),
        ]);
        $place1->expects($this->once())->method('isSame')->with($place3)->willReturn(false);
        $place2->expects($this->never())->method('isSame');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateSamePlaces(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', true],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('places')->willReturn([
            $place1 = $this->createMock(PlaceInterface::class),
            $place2 = $this->createMock(PlaceInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('places')->willReturn([
            $place3 = $this->createMock(PlaceInterface::class),
            $place4 = $this->createMock(PlaceInterface::class),
        ]);
        $place1->expects($this->once())->method('isSame')->with($place3)->willReturn(true);
        $place2->expects($this->once())->method('isSame')->with($place4)->willReturn(true);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(1, $model->getVersion());
    }

    public function testPreUpdateInvalidTransitions(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', false],
            ['transitions', true],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('transitions')->willReturn('old transitions');
        $args->expects($this->once())->method('getNewValue')->with('transitions')->willReturn('new transitions');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSameNumberOfTransitions(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', false],
            ['transitions', true],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('transitions')->willReturn([
            'transition 1',
            'transition 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('transitions')->willReturn([
            'transition 1',
            'transition 2',
            'transition 3',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateInvalidTransition(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', false],
            ['transitions', true],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('transitions')->willReturn([
            'transition 1',
            'transition 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('transitions')->willReturn([
            'transition 1',
            'transition 2',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSameTransition(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', false],
            ['transitions', true],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('transitions')->willReturn([
            $transition1 = $this->createMock(TransitionInterface::class),
            $transition2 = $this->createMock(TransitionInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('transitions')->willReturn([
            $transition3 = $this->createMock(TransitionInterface::class),
            $transition4 = $this->createMock(TransitionInterface::class),
        ]);
        $transition1->expects($this->once())->method('isSame')->with($transition3)->willReturn(false);
        $transition2->expects($this->never())->method('isSame');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateSameTransitions(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', false],
            ['places', false],
            ['transitions', true],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('transitions')->willReturn([
            $transition1 = $this->createMock(TransitionInterface::class),
            $transition2 = $this->createMock(TransitionInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('transitions')->willReturn([
            $transition3 = $this->createMock(TransitionInterface::class),
            $transition4 = $this->createMock(TransitionInterface::class),
        ]);
        $transition1->expects($this->once())->method('isSame')->with($transition3)->willReturn(true);
        $transition2->expects($this->once())->method('isSame')->with($transition4)->willReturn(true);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(1, $model->getVersion());
    }

    public function testPreUpdateInvalidStartCommands(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', true],
            ['places', false],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('startCommands')->willReturn('old start commands');
        $args->expects($this->once())->method('getNewValue')->with('startCommands')->willReturn('new start commands');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSameNumberOfStartCommands(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', true],
            ['places', false],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('startCommands')->willReturn([
            'command 1',
            'command 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('startCommands')->willReturn([
            'command 1',
            'command 2',
            'command 3',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateInvalidStartCommand(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', true],
            ['places', false],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('startCommands')->willReturn([
            'command 1',
            'command 2',
        ]);
        $args->expects($this->once())->method('getNewValue')->with('startCommands')->willReturn([
            'command 1',
            'command 2',
        ]);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateNotSameStartCommand(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', true],
            ['places', false],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('startCommands')->willReturn([
            $command1 = $this->createMock(CommandInterface::class),
            $command2 = $this->createMock(CommandInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('startCommands')->willReturn([
            $command3 = $this->createMock(CommandInterface::class),
            $command4 = $this->createMock(CommandInterface::class),
        ]);
        $command1->expects($this->once())->method('isSame')->with($command3)->willReturn(false);
        $command2->expects($this->never())->method('isSame');
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(2, $model->getVersion());
    }

    public function testPreUpdateSameStartCommands(): void
    {
        $model = new Model();
        $model->setVersion(1);
        $args = $this->createMock(PreUpdateEventArgs::class);
        $args->expects($this->once())->method('getEntity')->willReturn($model);
        $args->expects($this->exactly(3))->method('hasChangedField')->willReturnMap([
            ['startCommands', true],
            ['places', false],
            ['transitions', false],
        ]);
        $args->expects($this->once())->method('getOldValue')->with('startCommands')->willReturn([
            $command1 = $this->createMock(CommandInterface::class),
            $command2 = $this->createMock(CommandInterface::class),
        ]);
        $args->expects($this->once())->method('getNewValue')->with('startCommands')->willReturn([
            $command3 = $this->createMock(CommandInterface::class),
            $command4 = $this->createMock(CommandInterface::class),
        ]);
        $command1->expects($this->once())->method('isSame')->with($command3)->willReturn(true);
        $command2->expects($this->once())->method('isSame')->with($command4)->willReturn(true);
        $messageBus = $this->createMock(MessageBusInterface::class);
        $subscriber = new EntitySubscriber($messageBus);
        $subscriber->preUpdate($args);
        $this->assertSame(1, $model->getVersion());
    }
}
