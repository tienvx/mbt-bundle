<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
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
    protected MessageBusInterface|MockObject $messageBus;
    protected BugInterface $bug;
    protected array $pairs = [];

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bug = new Bug();
        $this->bug->setId(123);
        $this->bug->setMessage('Something wrong');
    }

    /**
     * @dataProvider stepsProvider
     */
    public function testDispatch(int $length, array $expectedPairs): void
    {
        if ($length > 0) {
            $this->bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(0, $length - 1)));
        }
        $this->messageBus
            ->expects($this->exactly(count($expectedPairs)))
            ->method('dispatch')
            ->with($this->assertMessage($length))
            ->willReturn(new Envelope(new \stdClass()));
        $this->assertSame(count($expectedPairs), $this->dispatcher->dispatch($this->bug));
        $this->assertPairs($expectedPairs);
    }

    protected function assertMessage(int $length): Callback
    {
        return $this->callback(function ($message) use ($length) {
            if (!$message instanceof ReduceStepsMessage) {
                return false;
            }
            $pair = [$message->getFrom(), $message->getTo()];
            if (!in_array($pair, $this->pairs)) {
                $this->pairs[] = $pair;
            }

            return $message->getBugId() === $this->bug->getId() &&
                $length === $message->getLength();
        });
    }

    abstract protected function assertPairs(array $expectedPairs): void;

    abstract public function stepsProvider(): array;
}
