<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReportBugMessage implements MessageInterface
{
    /**
     * @var int
     */
    protected $bugId;

    /**
     * @var array
     */
    protected $channels;

    public function __construct(int $bugId, array $channels)
    {
        $this->bugId = $bugId;
        $this->channels = $channels;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }
}
