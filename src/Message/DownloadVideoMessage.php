<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class DownloadVideoMessage implements MessageInterface
{
    protected int $bugId;
    protected string $videoUrl;

    public function __construct(int $bugId, string $videoUrl)
    {
        $this->bugId = $bugId;
        $this->videoUrl = $videoUrl;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }
}
