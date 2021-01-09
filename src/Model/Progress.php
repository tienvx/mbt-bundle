<?php

namespace Tienvx\Bundle\MbtBundle\Model;

class Progress implements ProgressInterface
{
    protected int $total = 0;

    protected int $processed = 0;

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function setProcessed(int $processed): void
    {
        $this->processed = $processed;
    }

    public function increase(int $processed = 1): void
    {
        $this->processed = min($this->total, $this->processed + $processed);
    }
}
