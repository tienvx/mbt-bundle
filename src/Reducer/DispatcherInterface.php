<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface DispatcherInterface
{
    public function dispatch(BugInterface $bug): int;
}
