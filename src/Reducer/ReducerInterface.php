<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ReducerInterface extends PluginInterface
{
    public function handle(BugInterface $bug, int $from, int $to): void;

    public function dispatch(BugInterface $bug): int;
}
