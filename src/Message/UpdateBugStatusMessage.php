<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class UpdateBugStatusMessage
{
    protected $id;
    protected $status;

    public function __construct(int $id, string $status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
