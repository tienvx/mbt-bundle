<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\ValueObject\Progress;
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
    protected TaskInterface $task;

    protected function setUp(): void
    {
        $progress = new Progress();
        $progress->setTotal(10);
        $progress->setProcessed(5);
        $this->task = new Task();
        $this->task->setProgress($progress);
    }

    public function testIncreaseProcessed(): void
    {
        $bugProgress = new TaskProgress();
        $bugProgress->increaseProcessed($this->task, 2);
        $this->assertSame(7, $this->task->getProgress()->getProcessed());
        $this->assertSame(10, $this->task->getProgress()->getTotal());
    }

    public function testIncreaseProcessedReachLimit(): void
    {
        $bugProgress = new TaskProgress();
        $bugProgress->increaseProcessed($this->task, 6);
        $this->assertSame(10, $this->task->getProgress()->getProcessed());
        $this->assertSame(10, $this->task->getProgress()->getTotal());
    }

    public function testSetTotal(): void
    {
        $taskProgress = new TaskProgress();
        $taskProgress->setTotal($this->task, 15);
        $this->assertSame(5, $this->task->getProgress()->getProcessed());
        $this->assertSame(15, $this->task->getProgress()->getTotal());
    }
}
