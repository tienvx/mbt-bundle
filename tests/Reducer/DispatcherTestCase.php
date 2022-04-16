<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\DispatcherInterface;

abstract class DispatcherTestCase extends TestCase
{
    protected DispatcherInterface $dispatcher;
    protected MessageBusInterface $messageBus;
    protected BugInterface $bug;
    protected array $pairs = [];

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bug = new Bug();
        $this->bug->setId(123);
        $this->bug->setMessage('Something wrong');
        $this->bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 11)));
    }

    public function testDispatchTooShortSteps(): void
    {
        $this->bug->setSteps([$this->createMock(StepInterface::class)]);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->assertSame(0, $this->dispatcher->dispatch($this->bug));
        $this->assertPairs(0);
    }

    public function testDispatch(): void
    {
        $this->messageBus
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->with($this->assertMessage())
            ->willReturn(new Envelope(new \stdClass()));
        $this->assertSame(4, $this->dispatcher->dispatch($this->bug));
        $this->assertPairs();
    }

    protected function assertMessage(): Callback
    {
        return $this->callback(function ($message) {
            if (!$message instanceof ReduceStepsMessage) {
                return false;
            }
            if (
                $message->getBugId() !== $this->bug->getId()
                || $message->getFrom() >= $message->getTo()
                || 11 !== $message->getLength()
            ) {
                return false;
            }
            $pair = [$message->getFrom(), $message->getTo()];
            if (!in_array($pair, $this->pairs)) {
                $this->pairs[] = $pair;

                return true;
            }

            return false;
        });
    }

    protected function assertPairs(int $count = 4): void
    {
        $this->assertCount($count, $this->pairs);
    }
}
