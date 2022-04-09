<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

class HandlerTestCase extends TestCase
{
    protected HandlerInterface $handler;
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected array $newSteps;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->bugRepository = $this->createMock(BugRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->stepsBuilder = $this->createMock(StepsBuilderInterface::class);
        $this->newSteps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $this->bug = new Bug();
        $this->bug->setId(1);
        $this->bug->setMessage('Something wrong');
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->stepsBuilder
            ->expects($this->once())
            ->method('create')
            ->with($this->bug, 1, 2)
            ->willReturn((fn () => yield from $this->newSteps)());
    }

    public function testHandleOldBug(): void
    {
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->stepsRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testHandle(?Throwable $exception, bool $updateSteps): void
    {
        $this->stepsRunner->expects($this->once())
            ->method('run')
            ->with(
                $this->newSteps,
                $this->bug,
                false,
                $this->callback(function (callable $exceptionCallback) use ($exception) {
                    if ($exception) {
                        $exceptionCallback($exception);
                    }

                    return true;
                })
            );
        if ($updateSteps) {
            $this->bugRepository
                ->expects($this->once())
                ->method('updateSteps')
                ->with($this->bug, $this->newSteps);
            $this->messageBus
                ->expects($this->once())
                ->method('dispatch')
                ->with($this->isInstanceOf(ReduceBugMessage::class))
                ->willReturn(new Envelope(new \stdClass()));
        } else {
            $this->bugRepository->expects($this->never())->method('updateSteps');
            $this->messageBus->expects($this->never())->method('dispatch');
        }
        $this->handler->handle($this->bug, 1, 2);
    }

    public function exceptionProvider(): array
    {
        return [
            [null, false],
            [new RuntimeException('Something else wrong'), false],
            [new Exception('Something wrong'), true],
        ];
    }
}
