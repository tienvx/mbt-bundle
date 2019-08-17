<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class CreateBugMessage
{
    protected $title;
    protected $path;
    protected $message;
    protected $taskId;
    protected $status;

    public function __construct(string $title, string $path, string $message, int $taskId, string $status)
    {
        $this->title = $title;
        $this->path = $path;
        $this->message = $message;
        $this->taskId = $taskId;
        $this->status = $status;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPath()
    {
        return $this->path;
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
