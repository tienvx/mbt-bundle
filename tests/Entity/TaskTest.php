<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Task;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 */
class TaskTest extends TestCase
{
    public function testPrePersist(): void
    {
        $task = new Task();
        $task->prePersist();
        $this->assertInstanceOf(\DateTime::class, $task->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $task->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $task = new Task();
        $task->prePersist();
        $task->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $task->getUpdatedAt());
    }
}
