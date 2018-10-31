<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReductionMessage
{
    protected $bugId;
    protected $reducer;
    protected $data;

    public function __construct(int $bugId, string $reducer, array $data)
    {
        $this->bugId   = $bugId;
        $this->reducer = $reducer;
        $this->data    = $data;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
