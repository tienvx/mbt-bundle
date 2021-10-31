<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Task;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\Color;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\ConfigInterface;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelper;
use Tienvx\Bundle\MbtBundle\Service\Task\TaskHelperInterface;
use Tienvx\Bundle\MbtBundle\Tests\StepsTestCase;
use Tienvx\Bundle\MbtBundle\ValueObject\Bug\Step;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Task\TaskHelper
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 */
class TaskHelperTest extends StepsTestCase
{
    protected array $steps;
    protected GeneratorManagerInterface $generatorManager;
    protected EntityManagerInterface $entityManager;
    protected StepRunnerInterface $stepRunner;
    protected BugHelperInterface $bugHelper;
    protected TaskHelperInterface $taskHelper;
    protected SelenoidHelperInterface $selenoidHelper;
    protected ConfigInterface $config;
    protected Connection $connection;
    protected DesiredCapabilities $capabilities;
    protected RemoteWebDriver $driver;
    protected Revision $revision;
    protected TaskInterface $task;
    protected string $session = 'f2bcd32b-d932-4cdc-a639-687ab8e4f840';

    protected function setUp(): void
    {
        $this->steps = [
            new Step([], new Color(), 0),
            new Step([], new Color(), 1),
            new Step([], new Color(), 2),
            new Step([], new Color(), 3),
        ];
        $this->generatorManager = $this->createMock(GeneratorManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->bugHelper = $this->createMock(BugHelperInterface::class);
        $this->selenoidHelper = $this->createMock(SelenoidHelperInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->taskHelper = new TaskHelper(
            $this->generatorManager,
            $this->entityManager,
            $this->stepRunner,
            $this->bugHelper,
            $this->selenoidHelper,
            $this->config
        );
        $this->driver = $this->createMock(RemoteWebDriver::class);
        $this->capabilities = new DesiredCapabilities();
        $this->revision = new Revision();
        $this->task = new Task();
        $this->task->setId(123);
        $this->task->setRunning(false);
        $this->task->setModelRevision($this->revision);
    }

    public function testRunNoTask(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not execute task 123: task not found');
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn(null);
        $this->taskHelper->run(123);
    }

    public function testRunAlreadyRunningTask(): void
    {
        $task = new Task();
        $task->setId(123);
        $task->setRunning(true);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Task 123 is already running');
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($task);
        $this->taskHelper->run(123);
    }

    public function testRun(): void
    {
        $this->config->expects($this->exactly(4))->method('getMaxSteps')->willReturn(150);
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($this->task)->willReturnCallback(
            fn () => yield from $this->steps
        );
        $this->driver->expects($this->once())->method('quit');
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->session);
        $this->config->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->generatorManager->expects($this->once())->method('getGenerator')->with('random')->willReturn($generator);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->task)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner
            ->expects($this->exactly(4))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver);
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($this->task);
        $this->entityManager->expects($this->exactly(2))->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->entityManager->expects($this->never())->method('persist');
        $this->taskHelper->run(123);
        $this->assertFalse($this->task->isRunning());
        $this->assertSame($this->session, $this->task->getSession());
    }

    public function testRunFoundBug(): void
    {
        $this->config->expects($this->exactly(2))->method('getMaxSteps')->willReturn(150);
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($this->task)->willReturnCallback(
            fn () => yield from $this->steps
        );
        $this->driver->expects($this->once())->method('quit');
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->session);
        $this->config->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->generatorManager->expects($this->once())->method('getGenerator')->with('random')->willReturn($generator);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->task)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner->expects($this->exactly(3))->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver)
            ->will($this->onConsecutiveCalls(
                null,
                null,
                $this->throwException(new Exception('Can not run the third step')),
            ));
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($this->task);
        $this->entityManager->expects($this->exactly(2))->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->bugHelper
            ->expects($this->once())
            ->method('createBug')
            ->with([$this->steps[0], $this->steps[1], $this->steps[2]], 'Can not run the third step')
            ->willReturn($bug = new Bug());

        $this->taskHelper->run(123);
        $this->assertFalse($this->task->isRunning());
        $this->assertSame([$bug], $this->task->getBugs()->toArray());
        $this->assertSame($this->session, $this->task->getSession());
    }

    public function testRunReachMaxSteps(): void
    {
        $this->config->expects($this->exactly(2))->method('getMaxSteps')->willReturn(2);
        $generator = $this->createMock(GeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($this->task)->willReturnCallback(
            fn () => yield from $this->steps
        );
        $this->driver->expects($this->once())->method('quit');
        $this->driver->expects($this->once())->method('getSessionID')->willReturn($this->session);
        $this->config->expects($this->once())->method('getGenerator')->willReturn('random');
        $this->generatorManager->expects($this->once())->method('getGenerator')->with('random')->willReturn($generator);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('getCapabilities')
            ->with($this->task)
            ->willReturn($this->capabilities);
        $this->selenoidHelper
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->capabilities)
            ->willReturn($this->driver);
        $this->stepRunner->expects($this->exactly(2))->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $this->driver);
        $this->entityManager->expects($this->once())->method('find')->with(Task::class, 123)->willReturn($this->task);
        $this->entityManager->expects($this->exactly(2))->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->entityManager->expects($this->never())->method('persist');
        $this->taskHelper->run(123);
        $this->assertFalse($this->task->isRunning());
        $this->assertSame($this->session, $this->task->getSession());
    }
}
