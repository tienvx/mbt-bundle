<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ExecuteTaskMessage
{
    /**
     * @var int
     */
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
