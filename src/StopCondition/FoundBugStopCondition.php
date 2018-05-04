<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class FoundBugStopCondition extends BaseStopCondition
{
    public function meet(array $context): bool
    {
        return $context['pathLength'] >= $this->maxPathLength;
    }

    public static function getName()
    {
        return 'found-bug';
    }
}
