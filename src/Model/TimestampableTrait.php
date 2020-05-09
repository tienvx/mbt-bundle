<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;

trait TimestampableTrait
{
    /**
     * @var ?DateTimeInterface
     */
    protected $updatedAt;

    /**
     * @var ?DateTimeInterface
     */
    protected $createdAt;

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
