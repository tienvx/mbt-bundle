<?php

namespace Tienvx\Bundle\MbtBundle\Tests\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;

/**
 * @covers \Tienvx\Bundle\MbtBundle\EventListener\EntitySubscriber
 *
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class EntitySubscriberTest extends TestCase
{
    protected MessageBusInterface|MockObject $messageBus;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $subscriber = new EntitySubscriber($this->messageBus);
        $this->assertSame([
            Events::postPersist,
        ], $subscriber->getSubscribedEvents());
    }

    public function testPostPersistBug(): void
    {
        $bug = new Bug();
        $bug->setId(23);
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn ($message) => $message instanceof ReduceBugMessage && 23 === $message->getId()))
            ->willReturn(new Envelope(new \stdClass()));
        $subscriber = new EntitySubscriber($this->messageBus);
        $args = new LifecycleEventArgs($bug, $this->createMock(ObjectManager::class));
        $subscriber->postPersist($args);
    }
}
