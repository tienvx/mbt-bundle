<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepository;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepositoryInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Repository\TaskRepository
 *
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Task
 * @uses \Tienvx\Bundle\MbtBundle\Model\Task
 * @uses \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @uses \Tienvx\Bundle\MbtBundle\Model\Bug
 */
class TaskRepositoryTest extends TestCase
{
    protected EntityManagerDecorator|MockObject $manager;
    protected TaskInterface $task;
    protected TaskRepositoryInterface $taskRepository;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(EntityManagerDecorator::class);
        $this->manager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(Task::class)
            ->willReturn($this->createMock(ClassMetadata::class));
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(Task::class)
            ->willReturn($this->manager);
        $this->taskRepository = new TaskRepository($managerRegistry);
        $this->task = new Task();
    }

    public function testStartRunningTask(): void
    {
        $this->task->setRunning(false);
        $this->manager->expects($this->once())->method('flush');
        $this->taskRepository->startRunning($this->task);
        $this->assertTrue($this->task->isRunning());
    }

    public function testStopRunningTask(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('connect');
        $this->task->setRunning(true);
        $this->manager->expects($this->once())->method('flush');
        $this->manager->expects($this->once())->method('getConnection')->willReturn($connection);
        $this->taskRepository->stopRunning($this->task);
        $this->assertFalse($this->task->isRunning());
    }

    public function testAddBug(): void
    {
        $bug = new Bug();
        $this->assertEmpty($this->task->getBugs());
        $this->manager->expects($this->once())->method('flush');
        $this->taskRepository->addBug($this->task, $bug);
        $this->assertSame([$bug], $this->task->getBugs()->toArray());
    }
}
