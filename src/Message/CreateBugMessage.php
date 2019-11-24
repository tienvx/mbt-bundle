<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class CreateBugMessage
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $steps;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int|null
     */
    protected $taskId;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $model;

    public function __construct(string $title, string $steps, string $message, ?int $taskId, string $status, string $model)
    {
        $this->title = $title;
        $this->steps = $steps;
        $this->message = $message;
        $this->taskId = $taskId;
        $this->status = $status;
        $this->model = $model;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSteps(): string
    {
        return $this->steps;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
