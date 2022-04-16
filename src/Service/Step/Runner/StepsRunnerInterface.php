<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Runner;

use Tienvx\Bundle\MbtBundle\Model\DebugInterface;

interface StepsRunnerInterface
{
    public function run(iterable $steps, DebugInterface $entity, callable $handleException): void;
}
