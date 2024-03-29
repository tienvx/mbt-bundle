<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\StepsNotConnectedException;
use Tienvx\Bundle\MbtBundle\Message\CreateBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Builder\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\BugStepsRunner;

abstract class HandlerTestCase extends TestCase
{
    protected HandlerInterface $handler;
    protected BugRepositoryInterface|MockObject $bugRepository;
    protected MessageBusInterface|MockObject $messageBus;
    protected BugStepsRunner|MockObject $stepsRunner;
    protected StepsBuilderInterface|MockObject $stepsBuilder;
    protected ConfigInterface|MockObject $config;
    protected array $newSteps;
    protected Revision $revision;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->bugRepository = $this->createMock(BugRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepsRunner = $this->createMock(BugStepsRunner::class);
        $this->stepsBuilder = $this->createMock(StepsBuilderInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
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
        $this->bug->setDebug(true);
        $this->revision = new Revision();
        $task = new Task();
        $task->setId(123);
        $task->setModelRevision($this->revision);
        $this->bug->setTask($task);
        $this->stepsBuilder
            ->expects($this->once())
            ->method('create')
            ->with($this->bug, 1, 2)
            ->willReturn((fn () => yield from $this->newSteps)());
    }

    public function testHandleNotConnectedSteps(): void
    {
        $this->expectStepsBuilder($this->throwException(new StepsNotConnectedException()));
        $this->stepsRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testHandleOldBug(): void
    {
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->expectStepsBuilder($this->returnValue((fn () => yield from $this->newSteps)()));
        $this->stepsRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testHandle(?Throwable $exception, bool $updateSteps, bool $createNewBug): void
    {
        $this->expectStepsBuilder($this->returnValue((fn () => yield from $this->newSteps)()));
        $this->stepsRunner->expects($this->once())
            ->method('run')
            ->with(
                $this->newSteps,
                $this->bug,
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
                ->with($this->callback(function ($message) {
                    return $message instanceof ReduceBugMessage && $message->getId() === $this->bug->getId();
                }))
                ->willReturn(new Envelope(new \stdClass()));
        } else {
            $this->bugRepository->expects($this->never())->method('updateSteps');
            if ($exception) {
                $this->config
                    ->expects($this->once())
                    ->method('shouldCreateNewBugWhileReducing')
                    ->willReturn($createNewBug);
                if ($createNewBug) {
                    $this->messageBus
                        ->expects($this->once())
                        ->method('dispatch')
                        ->with($this->callback(function ($message) use ($exception) {
                            return $message instanceof CreateBugMessage &&
                                $message->getMessage() === $exception->getMessage() &&
                                $message->getSteps() === $this->newSteps &&
                                123 === $message->getTaskId();
                        }))
                        ->willReturn(new Envelope(new \stdClass()));
                } else {
                    $this->messageBus->expects($this->never())->method('dispatch');
                }
            } else {
                $this->messageBus->expects($this->never())->method('dispatch');
            }
        }
        $this->handler->handle($this->bug, 1, 2);
        $this->assertFalse($this->bug->isDebug());
    }

    public function exceptionProvider(): array
    {
        return [
            [null, false, false],
            [new Exception('Something else wrong'), false, false],
            [new Exception('Something else wrong'), false, true],
            [new Exception('Something wrong'), true, false],
        ];
    }

    protected function expectStepsBuilder(Stub $will): void
    {
        $this->stepsBuilder
            ->expects($this->once())
            ->method('create')
            ->with($this->bug, 1, 2)
            ->will($will);
    }
}
