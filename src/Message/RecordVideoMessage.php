<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class RecordVideoMessage implements MessageInterface
{
    public function __construct(protected int $bugId)
    {
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }
}
