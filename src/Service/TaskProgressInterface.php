<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface TaskProgressInterface
{
    public function increaseProcessed(TaskInterface $task, int $processed = 1): void;

    public function setTotal(TaskInterface $task, int $total): void;

    public function flush(): void;
}
