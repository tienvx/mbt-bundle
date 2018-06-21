<?php

namespace Tienvx\Bundle\MbtBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ReducerFinishEvent extends Event
{
    /**
     * @var int
     */
    private $bugId;

    /**
     * @param int $bugId
     */
    public function __construct(int $bugId)
    {
        $this->bugId = $bugId;
    }

    /**
     * @return int
     */
    public function getBugId()
    {
        return $this->bugId;
    }
}
