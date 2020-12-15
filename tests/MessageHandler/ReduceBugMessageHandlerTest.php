<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceBugMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class ReduceBugMessageHandlerTest extends TestCase
{
    protected ReducerManager $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;
    protected ReduceBugMessageHandler $handler;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bugProgress = $this->createMock(BugProgressInterface::class);
        $this->handler = new ReduceBugMessageHandler(
            $this->reducerManager,
            $this->entityManager,
            $this->messageBus,
            $this->bugProgress
        );
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new ReduceBugMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeIncreaseTotalProgress(): void
    {
        $task = new Task();
        $task->getTaskConfig()->setReducer('random');
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(0);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setTask($task);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($bug)->willReturn(5);
        $this->reducerManager->expects($this->once())->method('get')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->bugProgress->expects($this->once())->method('increaseTotal')->with($bug, 5);
        $message = new ReduceBugMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeReportBug(): void
    {
        $task = new Task();
        $task->getTaskConfig()->setReducer('random');
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(10);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setId(123);
        $bug->setTask($task);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($bug)->willReturn(0);
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
        $this->bugProgress->expects($this->never())->method('increaseTotal');
        $message = new ReduceBugMessage(123);
        call_user_func($this->handler, $message);
    }
}
