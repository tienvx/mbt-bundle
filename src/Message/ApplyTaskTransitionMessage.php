<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ApplyTaskTransitionMessage implements MessageInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $transition;

    public function __construct(int $id, string $transition)
    {
        $this->id = $id;
        $this->transition = $transition;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }
}
