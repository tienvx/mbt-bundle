<?php

namespace Tienvx\Bundle\MbtBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class TaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function startRunning(TaskInterface $task): void
    {
        $task->setRunning(true);
        $this->getEntityManager()->flush();
    }

    public function stopRunning(TaskInterface $task): void
    {
        $task->setRunning(false);
        // Running task take long time. Reconnect to flush changes.
        $this->getEntityManager()->getConnection()->connect();
        $this->getEntityManager()->flush();
    }
}
