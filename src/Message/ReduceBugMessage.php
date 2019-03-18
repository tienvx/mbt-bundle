<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceBugMessage
{
    protected $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
