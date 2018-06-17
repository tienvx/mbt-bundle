<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

class MaxLengthStopCondition extends BaseStopCondition
{
    /**
     * @var int
     */
    protected $maxPathLength = 300;

    public function setMaxPathLength(int $maxPathLength)
    {
        $this->maxPathLength = $maxPathLength;
    }

    public function meet(array $context): bool
    {
        return $context['pathLength'] >= $this->maxPathLength;
    }

    public static function getName()
    {
        return 'max-length';
    }
}
