<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ExecuteTaskMessage implements MessageInterface
{
    protected int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
