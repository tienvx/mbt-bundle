<?php

namespace Tienvx\Bundle\MbtBundle\Service\Task;

interface TaskHelperInterface
{
    public function run(int $taskId): void;

    public function setMaxSteps(int $maxSteps): void;
}
