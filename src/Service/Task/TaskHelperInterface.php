<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

interface TaskHelperInterface
{
    public function run(int $taskId): void;
}
