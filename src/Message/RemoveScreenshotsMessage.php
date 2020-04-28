<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class RemoveScreenshotsMessage
{
    /**
     * @var int
     */
    protected $bugId;

    /**
     * @var string
     */
    protected $workflow;

    public function __construct(int $bugId, string $workflow)
    {
        $this->bugId = $bugId;
        $this->workflow = $workflow;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getWorkflow(): string
    {
        return $this->workflow;
    }
}
