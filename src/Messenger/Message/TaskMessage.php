<?php

namespace Tienvx\Bundle\MbtBundle\Messenger\Message;

class TaskMessage
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
