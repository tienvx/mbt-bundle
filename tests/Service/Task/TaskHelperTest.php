<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Task;

use Exception;
use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\Color;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelper;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Task\TaskHelper
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Model
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class TaskHelperTest extends TestCase
{
    protected array $steps;
    protected GeneratorManagerInterface $generatorManager;
    protected TaskRepositoryInterface $taskRepository;
    protected StepsRunnerInterface $stepsRunner;
    protected BugHelperInterface $bugHelper;
    protected TaskHelperInterface $taskHelper;
    protected ConfigInterface $config;
    protected TaskInterface $task;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->steps = [
            new Step([], new Color(), 0),
            new Step([], new Color(), 1),
            new Step([], new Color(), 2),
            new Step([], new Color(), 3),
        ];
        $this->generatorManager = $this->createMock(GeneratorManagerInterface::class);
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->taskHelper = new TaskHelper(
            $this->generatorManager,
            $this->taskRepository,
            $this->stepsRunner,
            $this->bugHelper,
            $this->config
        );
        $this->task = new Task();
        $this->task->setId(123);
        $this->task->setRunning(false);
        $this->task->setDebug(true);
        $this->bug = new Bug();
    }

    public function testRunNoTask(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not run task 123: task not found');
        $this->taskRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->taskHelper->run(123);
    }

    public function testRunTaskAlreadyRunning(): void
    {
        $this->expectException(RecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Can not run task 123: task is running. Will retry later');
        $this->task->setRunning(true);
        $this->taskRepository->expects($this->once())->method('find')->with(123)->willReturn($this->task);
        $this->taskHelper->run(123);
    }

    /**
     * @dataProvider stepProvider
     */
    public function testRun(?Throwable $exception, ?StepInterface $step): void
    {
        $this->taskRepository->expects($this->once())->method('find')->with(123)->willReturn($this->task);
        $this->taskRepository->expects($this->once())->method('startRunning')->with($this->task);
        $this->taskRepository->expects($this->once())->method('stopRunning')->with($this->task);
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($this->task)->willReturn($this->steps);
        $this->generatorManager->expects($this->once())->method('getGenerator')->with('random')->willReturn($generator);
        $this->config->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->stepsRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->steps,
                $this->task,
                $this->task->isDebug(),
                $this->callback(function (callable $exceptionCallback) use ($exception, $step) {
                    if ($exception) {
                        $exceptionCallback($exception, $step);
                    }

                    return true;
                }),
                $this->callback(function (callable $runCallback) use ($exception, $step) {
                    if (!$exception && $step) {
                        $this->assertFalse($runCallback($step));
                    }

                    return true;
                })
            );
        if ($exception) {
            $this->bugHelper
                ->expects($this->once())
                ->method('createBug')
                ->with($step ? [$step] : [], $exception->getMessage())
                ->willReturn($this->bug);
        } else {
            $this->bugHelper->expects($this->never())->method('createBug');
        }
        if (!$exception && $step) {
            $this->config
                ->expects($this->once())
                ->method('getMaxSteps')
                ->willReturn(150);
        } else {
            $this->config->expects($this->never())->method('getMaxSteps');
        }
        $this->taskHelper->run(123);
        if ($exception) {
            $this->assertSame([$this->bug], $this->task->getBugs()->toArray());
        } else {
            $this->assertEmpty($this->task->getBugs());
        }
    }

    public function stepProvider(): array
    {
        $step = new Step([], new Color(), 0);

        return [
            [null, null],
            [null, $step],
            [new Exception('Something wrong'), null],
            [new Exception('Caught a bug'), $step],
        ];
    }
}
