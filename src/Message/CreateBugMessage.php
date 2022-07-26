<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class CreateBugMessage implements MessageInterface
{
    public function __construct(
        protected int $taskId,
        protected array $steps,
        protected string $message
    ) {
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
