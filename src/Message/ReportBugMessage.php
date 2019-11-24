<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReportBugMessage
{
    /**
     * @var int
     */
    protected $bugId;

    /**
     * @var string
     */
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

    public function getReporter(): string
    {
        return $this->reporter;
    }
}
