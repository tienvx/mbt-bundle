<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface StepsRunnerInterface
{
    public function run(
        iterable $steps,
        TaskInterface|BugInterface $entity,
        bool $debug = false,
        ?callable $exceptionCallback = null,
        ?callable $runCallback = null
    ): void;
}
