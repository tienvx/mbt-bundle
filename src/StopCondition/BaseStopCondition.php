<?php

namespace Tienvx\Bundle\MbtBundle\StopCondition;

abstract class BaseStopCondition implements StopConditionInterface
{
    /**
     * @var int
     */
    protected $maxPathLength = 300;

    public function setMaxPathLength(int $maxPathLength)
    {
        $this->maxPathLength = $maxPathLength;
    }

    public function setArguments(array $arguments)
    {
    }
}
