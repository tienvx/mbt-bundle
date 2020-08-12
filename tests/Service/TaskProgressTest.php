<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\TaskProgress;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\TaskProgress
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 */
class TaskProgressTest extends TestCase
{
    protected EntityManagerInterface $entityManager;
    protected TaskInterface $task;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $this->task = new Task();
        $this->task->setProgress($progress);
    }

    public function testIncreaseProcessed(): void
    {
        $bugProgress = new TaskProgress($this->entityManager);
        $bugProgress->increaseProcessed($this->task, 2);
        $this->assertSame(7, $this->task->getProgress()->getProcessed());
        $this->assertSame(10, $this->task->getProgress()->getTotal());
    }

    public function testIncreaseProcessedReachLimit(): void
    {
        $bugProgress = new TaskProgress($this->entityManager);
        $bugProgress->increaseProcessed($this->task, 6);
        $this->assertSame(10, $this->task->getProgress()->getProcessed());
        $this->assertSame(10, $this->task->getProgress()->getTotal());
    }

    public function testSetTotal(): void
    {
        $taskProgress = new TaskProgress($this->entityManager);
        $taskProgress->setTotal($this->task, 15);
        $this->assertSame(5, $this->task->getProgress()->getProcessed());
        $this->assertSame(15, $this->task->getProgress()->getTotal());
    }

    public function testFlush(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($connection);
        $taskProgress = new TaskProgress($this->entityManager);
        $taskProgress->flush();
    }
}
