<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface HandlerInterface
{
    public function handle(BugInterface $bug, int $from, int $to): void;
}
