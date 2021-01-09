<?php

namespace Tienvx\Bundle\MbtBundle\Model;

interface ProgressInterface
{
    public function getTotal(): int;

    public function setTotal(int $total): void;

    public function getProcessed(): int;

    public function setProcessed(int $processed): void;

    public function increase(int $processed = 1): void;
}
