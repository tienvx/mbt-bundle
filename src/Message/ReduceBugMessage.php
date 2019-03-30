<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceBugMessage
{
    protected $id;
    protected $reducer;

    public function __construct(int $id, string $reducer)
    {
        $this->id = $id;
        $this->reducer = $reducer;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getReducer()
    {
        return $this->reducer;
    }
}
