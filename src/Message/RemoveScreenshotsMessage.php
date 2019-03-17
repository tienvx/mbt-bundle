<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class RemoveScreenshotsMessage
{
    protected $bugId;
    protected $model;

    public function __construct(int $bugId, string $model)
    {
        $this->bugId = $bugId;
        $this->model = $model;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
