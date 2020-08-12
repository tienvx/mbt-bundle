<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface BugProgressInterface
{
    public function increaseProcessed(BugInterface $bug, int $processed = 1): void;

    public function increaseTotal(BugInterface $bug, int $total): void;
}
