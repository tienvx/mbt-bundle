<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Service\BugProgressInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ReduceStepsMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class ReduceStepsMessageHandlerTest extends TestCase
{
    protected ReducerManager $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected ConfigLoaderInterface $configLoader;
    protected BugProgressInterface $bugProgress;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
        $this->bugProgress = $this->createMock(BugProgressInterface::class);
    }

    public function testInvokeNoBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('No bug found for id 123');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        $handler = new ReduceStepsMessageHandler($this->reducerManager, $this->entityManager, $this->messageBus, $this->configLoader, $this->bugProgress);
        $handler($message);
    }

    public function testInvokeReducedBug(): void
    {
        $bug = new Bug();
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 5)));
        $this->configLoader->expects($this->never())->method('getReducer');
        $this->reducerManager->expects($this->never())->method('get');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        $handler = new ReduceStepsMessageHandler($this->reducerManager, $this->entityManager, $this->messageBus, $this->configLoader, $this->bugProgress);
        $handler($message);
    }

    public function testInvokeIncreaseProcessedProgress(): void
    {
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 6)));
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($bug, 1, 2);
        $this->configLoader->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('get')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($bug, 1);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        $handler = new ReduceStepsMessageHandler($this->reducerManager, $this->entityManager, $this->messageBus, $this->configLoader, $this->bugProgress);
        $handler($message);
    }

    public function testInvokeReportBug(): void
    {
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(10);
        $bug = new Bug();
        $bug->setProgress($progress);
        $bug->setId(123);
        $bug->setSteps(array_map(fn () => $this->createMock(StepInterface::class), range(1, 6)));
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($bug, 1, 2);
        $this->configLoader->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('get')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->once())->method('dispatch')->with($this->callback(fn ($message) => $message instanceof ReportBugMessage && 123 === $message->getBugId()))->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($bug, 1);
        $message = new ReduceStepsMessage(123, 6, 1, 2);
        $handler = new ReduceStepsMessageHandler($this->reducerManager, $this->entityManager, $this->messageBus, $this->configLoader, $this->bugProgress);
        $handler($message);
    }
}
