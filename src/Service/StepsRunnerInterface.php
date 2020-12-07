<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface StepsRunnerInterface
{
    public function run(iterable $steps, TaskInterface $task, ?int $recordVideoBugId = null): iterable;
}
