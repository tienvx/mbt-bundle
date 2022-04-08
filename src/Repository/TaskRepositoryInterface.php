<?php

namespace Tienvx\Bundle\MbtBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface TaskRepositoryInterface extends ObjectRepository
{
    public function startRunning(TaskInterface $task): void;

    public function stopRunning(TaskInterface $task): void;
}
