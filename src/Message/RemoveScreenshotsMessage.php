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
