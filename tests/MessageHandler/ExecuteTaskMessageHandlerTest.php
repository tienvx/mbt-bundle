<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ExecuteTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgressInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\MessageHandler\ExecuteTaskMessageHandler
 * @covers \Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 */
class ExecuteTaskMessageHandlerTest extends TestCase
{
    protected GeneratorManager $generatorManager;
    protected EntityManagerInterface $entityManager;
    protected StepsRunnerInterface $stepsRunner;
    protected TaskProgressInterface $taskProgress;
    protected TranslatorInterface $translator;
    protected Connection $connection;
    protected ExecuteTaskMessageHandler $handler;

    protected function setUp(): void
    {
        $this->generatorManager = $this->createMock(GeneratorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->taskProgress = $this->createMock(TaskProgressInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->handler = new ExecuteTaskMessageHandler(
            $this->generatorManager,
            $this->entityManager,
            $this->stepsRunner,
            $this->taskProgress,
            $this->translator
        );
        $this->handler->setMaxSteps(150);
    }

    public function testInvokeNoTask(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not execute task 123: task not found');
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn(null);
        $message = new ExecuteTaskMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvoke(): void
    {
        $model = new Model();
        $task = new Task();
        $task->setModel($model);
        $task->getTaskConfig()->setGenerator('random');
        $steps = array_fill(0, 4, $this->createMock(StepInterface::class));
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($task)->willReturnCallback(
            function () use ($steps): iterable {
                foreach ($steps as $step) {
                    yield $step;
                }
            }
        );
        $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
        $this->stepsRunner->expects($this->once())->method('run')->willReturnCallback(fn ($iterable) => $iterable);
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($task);
        $this->taskProgress->expects($this->once())->method('setTotal')->with($task, 150);
        $this->taskProgress->expects($this->exactly(4))->method('increaseProcessed')->with($task, 1);
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->entityManager->expects($this->never())->method('persist');
        $message = new ExecuteTaskMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeFoundBug(): void
    {
        $model = new Model();
        $model->setVersion(123);
        $model->setLabel('Model label');
        $task = new Task();
        $task->setModel($model);
        $task->getTaskConfig()->setGenerator('random');
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($task)->willReturnCallback(
            function () use ($steps): iterable {
                foreach ($steps as $step) {
                    yield $step;
                }
            }
        );
        $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
        $this->stepsRunner->expects($this->once())->method('run')->willReturnCallback(
            function () use ($steps): iterable {
                $count = 0;
                foreach ($steps as $step) {
                    ++$count;
                    yield $step;
                    if (3 === $count) {
                        throw new Exception('Can not run the third step');
                    }
                }
            }
        );
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($task);
        $this->taskProgress->expects($this->once())->method('setTotal')->with($task, 150);
        $this->taskProgress->expects($this->exactly(3))->method('increaseProcessed')->with($task, 1);
        $this->entityManager->expects($this->once())->method('persist')->with(
            $this->callback(function ($bug) use ($steps, $task) {
                return $bug instanceof BugInterface
                    && $bug->getSteps() === [$steps[0], $steps[1], $steps[2]]
                    && 'Can not run the third step' === $bug->getMessage()
                    && $bug->getTask() === $task
                    && $bug->getModelVersion() === $task->getModel()->getVersion()
                    && 'Translated default bug title' === $bug->getTitle();
            })
        );
        $this->entityManager->expects($this->once())->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('mbt.default_bug_title', ['%model%' => 'Model label'])
            ->willReturn('Translated default bug title');

        $message = new ExecuteTaskMessage(123);
        call_user_func($this->handler, $message);
    }

    public function testInvokeReachMaxSteps(): void
    {
        $this->handler->setMaxSteps(2);
        $model = new Model();
        $task = new Task();
        $task->setModel($model);
        $task->getTaskConfig()->setGenerator('random');
        $steps = array_fill(0, 4, $this->createMock(StepInterface::class));
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($task)->willReturnCallback(
            function () use ($steps): iterable {
                foreach ($steps as $step) {
                    yield $step;
                }
            }
        );
        $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
        $this->stepsRunner->expects($this->once())->method('run')->willReturnCallback(fn ($iterable) => $iterable);
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($task);
        $this->taskProgress->expects($this->once())->method('setTotal')->with($task, 2);
        $this->taskProgress->expects($this->exactly(2))->method('increaseProcessed')->with($task, 1);
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->entityManager->expects($this->never())->method('persist');
        $message = new ExecuteTaskMessage(123);
        call_user_func($this->handler, $message);
    }
}
