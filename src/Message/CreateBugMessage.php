<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class CreateBugMessage
{
    protected $title;
    protected $steps;
    protected $message;
    protected $taskId;
    protected $status;

    public function __construct(string $title, string $steps, string $message, int $taskId, string $status)
    {
        $this->title = $title;
        $this->steps = $steps;
        $this->message = $message;
        $this->taskId = $taskId;
        $this->status = $status;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getTaskId()
    {
        return $this->taskId;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
