<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Steps;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\DispatcherInterface;

class DispatcherTestCase extends TestCase
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
        $steps = new Steps();
        $steps->setSteps(array_fill(0, 11, $this->createMock(StepInterface::class)));
        $this->bug->setSteps($steps);
    }

    public function testDispatch(): void
    {
        $this->messageBus->expects($this->exactly(3))->method('dispatch')->with($this->assertMessage())->willReturn(new Envelope(new \stdClass()));
        $this->assertSame(3, $this->dispatcher->dispatch($this->bug));
        $this->assertPairs();
    }

    protected function assertMessage(): Callback
    {
        return $this->callback(function ($message) {
            if (!$message instanceof ReduceStepsMessage) {
                return false;
            }
            if ($message->getBugId() !== $this->bug->getId() || $message->getFrom() >= $message->getTo() || 11 !== $message->getLength()) {
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

    protected function assertPairs(): void
    {
        $this->assertCount(3, $this->pairs);
    }
}
