<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

interface HandlerInterface
{
    public function handle(Bug $bug, int $length, int $from, int $to): void;

    public static function getReducerName(): string;
}
