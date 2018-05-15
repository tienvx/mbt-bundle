<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReproducePathMessage
{
    protected $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
