<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ReducerInterface extends PluginInterface
{
    public function handle(Bug $bug, int $length, int $from, int $to): void;

    public function dispatch(Bug $bug): int;
}
