<?php

namespace Tienvx\Bundle\MbtBundle\Tests\MessageHandler;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Message\ExecuteTaskMessage;
use Tienvx\Bundle\MbtBundle\MessageHandler\ExecuteTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Service\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigLoaderInterface;
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
 */
class ExecuteTaskMessageHandlerTest extends TestCase
{
    protected GeneratorManager $generatorManager;
    protected EntityManagerInterface $entityManager;
    protected StepsRunnerInterface $stepsRunner;
    protected ConfigLoaderInterface $configLoader;
    protected TaskProgressInterface $taskProgress;
    protected BugHelperInterface $bugHelper;
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->generatorManager = $this->createMock(GeneratorManager::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->configLoader = $this->createMock(ConfigLoaderInterface::class);
        $this->taskProgress = $this->createMock(TaskProgressInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->connection = $this->createMock(Connection::class);
    }

    public function testInvokeNoTask(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not execute task 123: task not found');
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn(null);
        $message = new ExecuteTaskMessage(123);
        $handler = new ExecuteTaskMessageHandler($this->generatorManager, $this->entityManager, $this->stepsRunner, $this->configLoader, $this->taskProgress, $this->bugHelper);
        $handler($message);
    }

    public function testInvoke(): void
    {
        $petrinet = new Petrinet();
        $model = new Model();
        $model->setPetrinet($petrinet);
        $task = new Task();
        $task->setModel($model);
        $steps = array_fill(0, 4, $this->createMock(StepInterface::class));
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($petrinet)->willReturnCallback(
            function () use ($steps): iterable {
                foreach ($steps as $step) {
                    yield $step;
                }
            }
        );
        $this->configLoader->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->configLoader->expects($this->once())->method('getMaxSteps')->willReturn(150);
        $this->generatorManager->expects($this->once())->method('get')->with('random')->willReturn($generator);
        $this->stepsRunner->expects($this->once())->method('run')->willReturnCallback(fn ($iterable) => $iterable);
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($task);
        $this->taskProgress->expects($this->once())->method('setTotal')->with($task, 150);
        $this->taskProgress->expects($this->exactly(4))->method('increaseProcessed')->with($task, 1);
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->bugHelper->expects($this->never())->method('create');
        $message = new ExecuteTaskMessage(123);
        $handler = new ExecuteTaskMessageHandler($this->generatorManager, $this->entityManager, $this->stepsRunner, $this->configLoader, $this->taskProgress, $this->bugHelper);
        $handler($message);
    }

    public function testInvokeFoundBug(): void
    {
        $petrinet = new Petrinet();
        $model = new Model();
        $model->setPetrinet($petrinet);
        $task = new Task();
        $task->setModel($model);
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $bug = new Bug();
        $bug->setSteps($steps);
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($petrinet)->willReturnCallback(
            function () use ($steps): iterable {
                foreach ($steps as $step) {
                    yield $step;
                }
            }
        );
        $this->configLoader->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->configLoader->expects($this->once())->method('getMaxSteps')->willReturn(150);
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
        $this->entityManager->expects($this->once())->method('persist')->with($bug);
        $this->entityManager->expects($this->once())->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->bugHelper->expects($this->once())->method('create')->with([$steps[0], $steps[1], $steps[2]], 'Can not run the third step', $model)->willReturn($bug);

        $message = new ExecuteTaskMessage(123);
        $handler = new ExecuteTaskMessageHandler($this->generatorManager, $this->entityManager, $this->stepsRunner, $this->configLoader, $this->taskProgress, $this->bugHelper);
        $handler($message);
    }
}
