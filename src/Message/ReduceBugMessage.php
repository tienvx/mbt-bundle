<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceBugMessage implements MessageInterface
{
    public function __construct(protected int $id)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
