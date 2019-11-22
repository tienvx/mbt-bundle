<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

interface HandlerInterface
{
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to): void;

    public static function getReducerName(): string;
}
