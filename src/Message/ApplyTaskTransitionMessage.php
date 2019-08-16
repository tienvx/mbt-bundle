<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ApplyTaskTransitionMessage
{
    protected $id;
    protected $transition;

    public function __construct(int $id, string $transition)
    {
        $this->id = $id;
        $this->transition = $transition;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTransition()
    {
        return $this->transition;
    }
}
