<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class FoundBugStopCondition implements StopConditionInterface
{
    public function setArguments(array $arguments)
    {
    }

    public function meet(array $context): bool
    {
        return $context['pathLength'] >= 500;
    }

    public static function getName()
    {
        return 'found-bug';
    }
}
