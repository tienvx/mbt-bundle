<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceStepsMessage implements MessageInterface
{
    protected int $bugId;
    protected int $length;
    protected int $from;
    protected int $to;

    public function __construct(int $bugId, int $length, int $from, int $to)
    {
        $this->bugId = $bugId;
        $this->length = $length;
        $this->from = $from;
        $this->to = $to;
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
