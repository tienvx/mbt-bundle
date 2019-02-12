<?php

namespace Tienvx\Bundle\MbtBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

class ReducerFinishEvent extends Event
{
    /**
     * @var Bug
     */
    private $bug;

    /**
     * @param Bug $bug
     */
    public function __construct(Bug $bug)
    {
        $this->bug = $bug;
    }

    /**
     * @return Bug
     */
    public function getBug()
    {
        return $this->bug;
    }
}
