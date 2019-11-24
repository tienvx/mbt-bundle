<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class CaptureScreenshotsMessage
{
    /**
     * @var int
     */
    protected $bugId;

    public function __construct(int $bugId)
    {
        $this->bugId = $bugId;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }
}
