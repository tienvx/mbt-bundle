<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

interface DispatcherInterface
{
    public function dispatch(Bug $bug): int;

    public static function getReducerName(): string;
}
