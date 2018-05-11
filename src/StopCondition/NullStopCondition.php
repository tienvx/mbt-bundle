<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class NullStopCondition extends BaseStopCondition
{
    public function meet(array $context): bool
    {
        return false;
    }

    public static function getName()
    {
        return 'null';
    }
}
