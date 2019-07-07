<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReportBugMessage
{
    protected $bugId;
    protected $reporter;

    public function __construct(int $bugId, string $reporter)
    {
        $this->bugId = $bugId;
        $this->reporter = $reporter;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getReporter()
    {
        return $this->reporter;
    }
}
