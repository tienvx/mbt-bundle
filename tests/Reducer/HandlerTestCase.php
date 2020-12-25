<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;

class HandlerTestCase extends StepsTestCase
{
    protected HandlerInterface $handler;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected array $newSteps;
    protected BugInterface $bug;
    protected TaskInterface $task;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->stepsBuilder = $this->createMock(StepsBuilderInterface::class);
        $this->newSteps = array_map(fn () => $this->createMock(StepInterface::class), range(1, 4));
        $model = $this->createMock(ModelInterface::class);
        $this->task = new Task();
        $this->task->setModel($model);
        $this->bug = new Bug();
        $this->bug->setId(1);
        $this->bug->setMessage('Something wrong');
        $this->bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 5)));
        $this->bug->setTask($this->task);
        $this->stepsBuilder
            ->expects($this->once())
            ->method('create')
            ->with($this->bug, 1, 2)
            ->willReturn($this->newSteps);
    }

    public function testHandleOldBug(): void
    {
        $this->bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 3)));
        $this->stepsRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRun(): void
    {
        $this->stepsRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->newSteps, $this->bug->getTask())
            ->willReturnCallback(
                function (): iterable {
                    foreach ($this->newSteps as $step) {
                        yield $step;
                    }
                }
            );
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunIntoException(): void
    {
        $this->stepsRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->newSteps)
            ->willThrowException(new RuntimeException('Something else wrong'));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something else wrong');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunFoundSameBug(): void
    {
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager
            ->expects($this->once())
            ->method('lock')
            ->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager
            ->expects($this->once())
            ->method('transactional')
            ->with($this->callback(function ($callback) {
                $callback();

                return true;
            }));
        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ReduceBugMessage::class))
            ->willReturn(new Envelope(new \stdClass()));
        $this->stepsRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->newSteps)
            ->willThrowException(new Exception('Something wrong'));
        $this->handler->handle($this->bug, 1, 2);
        $this->assertSteps($this->newSteps, $this->bug->getSteps());
    }
}
