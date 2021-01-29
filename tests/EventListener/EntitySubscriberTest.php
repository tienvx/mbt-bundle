<?php

namespace Tienvx\Bundle\MbtBundle\Tests\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;

/**
 * @covers \Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class EntitySubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new EntitySubscriber($this->createMock(MessageBusInterface::class));
        $this->assertSame([
            Events::postPersist,
        ], $subscriber->getSubscribedEvents());
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
}
