<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceStepsMessage implements MessageInterface
{
    public function __construct(
        protected int $bugId,
        protected int $length,
        protected int $from,
        protected int $to
    ) {
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }
}
