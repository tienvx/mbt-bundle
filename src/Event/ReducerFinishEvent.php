<?php

namespace Tienvx\Bundle\MbtBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ReducerFinishEvent extends Event
{
    /**
     * @var int
     */
    private $reproducePathId;

    /**
     * @param int $reproducePathId
     */
    public function __construct(int $reproducePathId)
    {
        $this->taskId = $reproducePathId;
    }

    /**
     * @return int
     */
    public function getReproducePathId()
    {
        return $this->reproducePathId;
    }
}
