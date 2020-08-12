<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class TaskProgress implements TaskProgressInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function increaseProcessed(TaskInterface $task, int $processed = 1): void
    {
        $task->getProgress()->setProcessed(min($task->getProgress()->getTotal(), $task->getProgress()->getProcessed() + $processed));
    }

    public function setTotal(TaskInterface $task, int $total): void
    {
        $task->getProgress()->setTotal($total);
    }

    public function flush(): void
    {
        // Executing task take long time. Reconnect database to update task progress.
        $this->entityManager->getConnection()->connect();

        $this->entityManager->flush();
    }
}
