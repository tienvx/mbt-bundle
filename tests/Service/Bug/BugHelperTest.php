<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Bug;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugNotifierInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugProgressInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @uses \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 */
class BugHelperTest extends TestCase
{
    protected ReducerManagerInterface $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;
    protected BugNotifierInterface $notifyHelper;
    protected StepRunnerInterface $stepRunner;
    protected SelenoidHelperInterface $selenoidHelper;
    protected ConfigInterface $config;
    protected BugHelperInterface $helper;
    protected Connection $connection;
    protected TaskInterface $task;
    protected BugInterface $bug;
    protected ProgressInterface $progress;
    protected RevisionInterface $revision;
    protected DesiredCapabilities $capabilities;
    protected RemoteWebDriver $driver;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bugProgress = $this->createMock(BugProgressInterface::class);
        $this->notifyHelper = $this->createMock(BugNotifierInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->selenoidHelper = $this->createMock(SelenoidHelperInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->helper = new BugHelper(
            $this->reducerManager,
            $this->entityManager,
            $this->messageBus,
            $this->bugProgress,
            $this->notifyHelper,
            $this->stepRunner,
            $this->selenoidHelper,
            $this->config
        );
        $this->connection = $this->createMock(Connection::class);
        $this->revision = new Revision();
        $this->task = new Task();
        $this->task->setModelRevision($this->revision);
        $this->progress = new Progress();
        $this->progress->setTotal(10);
        $this->progress->setProcessed(9);
        $this->bug = new Bug();
        $this->bug->setProgress($this->progress);
        $this->bug->setId(123);
        $this->bug->setTask($this->task);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->capabilities = new DesiredCapabilities();
    }

    public function testCreateBug(): void
    {
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $bug = $this->helper->createBug($steps, 'Something wrong');
        $this->assertInstanceOf(BugInterface::class, $bug);
        $this->assertSame($steps, $bug->getSteps());
        $this->assertSame('', $bug->getTitle());
        $this->assertSame('Something wrong', $bug->getMessage());
    }

    public function testReduceMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reduceBug(123);
    }

    public function testReduceBugDispatchMessages(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(5);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->once())->method('increaseTotal')->with($this->bug, 5);
        $this->helper->reduceBug(123);
    }

    public function testReduceBugNotDispatchMessages(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(0);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->never())->method('increaseTotal');
        $this->helper->reduceBug(123);
    }

    public function testFinishReduceBug(): void
    {
        $this->progress->setProcessed(10);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(0);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->config->expects($this->once())->method('shouldReportBug')->willReturn(true);
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
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->never())->method('increaseTotal');
        $this->helper->reduceBug(123);
    }

    public function testReportMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not report bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reportBug(123);
    }

    public function testReportBug(): void
    {
        $task = new Task();
        $task->setAuthor(22);
        $bug = new Bug();
        $bug->setTitle('New bug found');
        $bug->setId(123);
        $bug->setMessage('Something wrong');
        $bug->setTask($task);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->notifyHelper
            ->expects($this->once())
            ->method('notify')
            ->with($bug);
        $this->helper->reportBug(123);
    }

    public function testReduceStepsMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce steps for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reduceSteps(123, 6, 1, 2);
    }

    public function testReduceReducedSteps(): void
    {
        $this->reducerManager->expects($this->never())->method('getReducer');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testNotStopReduceSteps(): void
    {
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($this->bug, 1);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testStopReduceStepsAndRecordVideo(): void
    {
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with($this->callback(
                fn ($message) => $message instanceof RecordVideoMessage && 123 === $message->getBugId()
            ))
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress
            ->expects($this->once())
            ->method('increaseProcessed')
            ->with($this->bug, 1)
            ->willReturnCallback(fn () => $this->progress->setProcessed(10));
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testStopReduceStepsAndReportBug(): void
    {
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->config->expects($this->once())->method('shouldReportBug')->willReturn(true);
        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(
                        fn ($message) => $message instanceof RecordVideoMessage && 123 === $message->getBugId()
                    ),
                ],
                [
                    $this->callback(
                        fn ($message) => $message instanceof ReportBugMessage && 123 === $message->getBugId()
                    ),
                ]
            )
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress
            ->expects($this->once())
            ->method('increaseProcessed')
            ->with($this->bug, 1)
            ->willReturnCallback(fn () => $this->progress->setProcessed(10));
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testRecordVideoMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideoThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exception that we care about');
        $this->driver->expects($this->once())->method('quit');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->bug, true)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver)
            ->willThrowException(new RuntimeException('Exception that we care about'));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideoNotThrowException(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->bug, true)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner
            ->expects($this->exactly(2))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver)
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new Exception("Exception that we don't care about")),
            );
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideo(): void
    {
        $this->driver->expects($this->once())->method('quit');
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->bug, true)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner
            ->expects($this->exactly(3))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }
}
