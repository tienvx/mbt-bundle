<?php

namespace Tienvx\Bundle\MbtBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class ReducerFinishEvent extends Event
{
    /**
     * @var string
     */
    private $bugMessage;

    /**
     * @var Path
     */
    private $path;

    /**
     * @var mixed
     */
    private $taskId;

    /**
     * @param string $bugMessage
     * @param Path $path
     * @param mixed $taskId
     */
    public function __construct(string $bugMessage, Path $path, $taskId = null)
    {
        $this->bugMessage = $bugMessage;
        $this->path       = $path;
        $this->taskId     = $taskId;
    }

    public function getBugMessage(): string
    {
        return $this->bugMessage;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->taskId;
    }
}
