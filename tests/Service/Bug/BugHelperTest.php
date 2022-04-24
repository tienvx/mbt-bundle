<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Bug;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Repository\BugRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugNotifierInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\Step\Runner\RecordStepsRunner;
use Tienvx\Bundle\MbtBundle\Service\Step\StepHelperInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Progress
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Video
 * @uses \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @uses \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 * @uses \Tienvx\Bundle\MbtBundle\Model\Debug
 */
class BugHelperTest extends TestCase
{
    protected ReducerManagerInterface $reducerManager;
    protected BugRepositoryInterface $bugRepository;
    protected MessageBusInterface $messageBus;
    protected BugNotifierInterface $bugNotifier;
    protected StepHelperInterface $stepHelper;
    protected RecordStepsRunner $stepsRunner;
    protected ConfigInterface $config;
    protected BugHelperInterface $helper;
    protected Revision $revision;
    protected BugInterface $bug;
    protected ProgressInterface $progress;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManagerInterface::class);
        $this->bugRepository = $this->createMock(BugRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bugNotifier = $this->createMock(BugNotifierInterface::class);
        $this->stepHelper = $this->createMock(StepHelperInterface::class);
        $this->stepsRunner = $this->createMock(RecordStepsRunner::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->helper = new BugHelper(
            $this->reducerManager,
            $this->bugRepository,
            $this->messageBus,
            $this->bugNotifier,
            $this->stepHelper,
            $this->stepsRunner,
            $this->config
        );
        $this->progress = new Progress();
        $this->progress->setTotal(10);
        $this->progress->setProcessed(9);
        $this->bug = new Bug();
        $this->bug->setProgress($this->progress);
        $this->bug->setMessage('Something wrong');
        $this->bug->setId(123);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->bug->setDebug(false);
        $this->revision = new Revision();
        $task = new Task();
        $task->setModelRevision($this->revision);
        $this->bug->setTask($task);
    }

    public function testReduceMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce bug 123: bug not found');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->helper->reduceBug(123);
    }

    public function testReduceBugDispatchMessages(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(5);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->bugRepository->expects($this->once())->method('increaseTotal')->with($this->bug, 5);
        $this->helper->reduceBug(123);
    }

    public function testReduceBugNotDispatchMessages(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(0);
        $this->config->expects($this->once())->method('getReducer')->willReturn('random');
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->bugRepository->expects($this->never())->method('increaseTotal');
        $this->helper->reduceBug(123);
    }

    public function testReduceBugRecordAndReport(): void
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
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->bugRepository->expects($this->never())->method('increaseTotal');
        $this->helper->reduceBug(123);
    }

    public function testReportMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not report bug 123: bug not found');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->helper->reportBug(123);
    }

    public function testReportBug(): void
    {
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->bugNotifier
            ->expects($this->once())
            ->method('notify')
            ->with($this->bug);
        $this->helper->reportBug(123);
    }

    public function testReduceStepsMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce steps for bug 123: bug not found');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->helper->reduceSteps(123, 6, 1, 2);
    }

    public function testReduceReducedSteps(): void
    {
        $this->reducerManager->expects($this->never())->method('getReducer');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    /**
     * @dataProvider messageProvider
     */
    public function testReduceSteps(int $processed, bool $reportBug, array $messages): void
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
        if ($processed < $this->progress->getTotal()) {
            $this->messageBus->expects($this->never())->method('dispatch');
            $this->config->expects($this->never())->method('shouldReportBug');
        } else {
            $this->messageBus
                ->expects($this->exactly(count($messages)))
                ->method('dispatch')
                ->withConsecutive(...array_map(fn (string $className) => [$this->callback(
                    fn ($message) => is_a($message, $className) && 123 === $message->getBugId()
                )], $messages))
                ->willReturn(new Envelope(new \stdClass()));
            $this->config->expects($this->once())->method('shouldReportBug')->willReturn($reportBug);
        }
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->bugRepository
            ->expects($this->once())
            ->method('increaseProcessed')
            ->with($this->bug, 1)
            ->willReturnCallback(fn () => $this->progress->setProcessed($processed));
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function messageProvider(): array
    {
        return [
            [9, true, []],
            [10, false, [RecordVideoMessage::class]],
            [10, true, [RecordVideoMessage::class, ReportBugMessage::class]],
        ];
    }

    public function testRecordVideoMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug not found');
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideoAlreadyRecording(): void
    {
        $this->expectException(RecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug is recording. Will retry later');
        $this->bug->getVideo()->setRecording(true);
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testRecordVideo(?Throwable $exception, ?string $expectedVideoErrorMessage): void
    {
        $this->stepsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->bug->getSteps(),
                $this->bug,
                $this->callback(function (callable $exceptionCallback) use ($exception) {
                    if ($exception) {
                        $exceptionCallback($exception);
                    }

                    return true;
                })
            );
        $this->bugRepository->expects($this->once())->method('find')->with(123)->willReturn($this->bug);
        $this->stepHelper
            ->expects($this->once())
            ->method('cloneAndResetSteps')
            ->with($this->bug->getSteps(), $this->revision)
            ->willReturnArgument(0);
        $this->bugRepository->expects($this->once())->method('startRecording')->with($this->bug);
        $this->bugRepository->expects($this->once())->method('stopRecording')->with($this->bug);
        $this->helper->recordVideo(123);
        $this->assertTrue($this->bug->isDebug());
        $this->assertSame($expectedVideoErrorMessage, $this->bug->getVideo()->getErrorMessage());
    }

    public function exceptionProvider(): array
    {
        return [
            [null, null],
            [new Exception('Something wrong'), null],
            [new Exception('Something else wrong'), 'Something else wrong'],
        ];
    }
}
