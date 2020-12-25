<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class ReduceStepsMessageHandlerTest extends TestCase
{
    protected ReducerManager $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;
    protected TaskInterface $task;
    protected ReduceStepsMessageHandler $handler;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bugProgress = $this->createMock(BugProgressInterface::class);
        $model = new Model();
        $model->setVersion(1);
        $this->task = new Task();
        $this->task->setModel($model);
        $this->handler = new ReduceStepsMessageHandler(
            $this->reducerManager,
            $this->entityManager,
            $this->messageBus,
            $this->bugProgress
        );
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce steps for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        call_user_func($this->handler, $message);
    }

    public function testInvokeBugWithModifiedModel(): void
    {
        $this->task->getModel()->setVersion(2);
        $bug = new Bug();
        $bug->setModelVersion(1);
        $bug->setTask($this->task);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 5)));
        $this->reducerManager->expects($this->never())->method('get');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        call_user_func($this->handler, $message);
    }

    public function testInvokeReducedBug(): void
    {
        $bug = new Bug();
        $bug->setModelVersion(1);
        $bug->setTask($this->task);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 5)));
        $this->reducerManager->expects($this->never())->method('get');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        call_user_func($this->handler, $message);
    }

    public function testInvokeIncreaseProcessedProgress(): void
    {
        $this->task->getTaskConfig()->setReducer('random');
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setModelVersion(1);
        $bug->setTask($this->task);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 6)));
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($bug, 1, 2);
        $this->reducerManager->expects($this->once())->method('get')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($bug, 1);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        call_user_func($this->handler, $message);
    }

    public function testInvokeReportBug(): void
    {
        $this->task->getTaskConfig()->setReducer('random');
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(10);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setId(123);
        $bug->setModelVersion(1);
        $bug->setTask($this->task);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 6)));
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($bug, 1, 2);
        $this->reducerManager->expects($this->once())->method('get')->with('random')->willReturn($reducer);
        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function ($message) {
                return (
                    $message instanceof RecordVideoMessage
                    || $message instanceof ReportBugMessage
                )
                && 123 === $message->getBugId();
            }))
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($bug, 1);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        call_user_func($this->handler, $message);
    }
}
