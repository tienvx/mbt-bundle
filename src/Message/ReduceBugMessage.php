<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceBugMessage implements MessageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $reducer;

    public function __construct(int $id, string $reducer)
    {
        $this->id = $id;
        $this->reducer = $reducer;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }
}
