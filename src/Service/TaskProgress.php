<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class TaskProgress implements TaskProgressInterface
{
    public function increaseProcessed(TaskInterface $task, int $processed = 1): void
    {
        $task->getProgress()->setProcessed(min(
            $task->getProgress()->getTotal(),
            $task->getProgress()->getProcessed() + $processed
        ));
    }

    public function setTotal(TaskInterface $task, int $total): void
    {
        $task->getProgress()->setTotal($total);
    }
}
