<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\Message\QueuedPathReducerMessage;

interface QueuedPathReducerInterface
{
    public function handle(QueuedPathReducerMessage $queuedPathReducerMessage);

    public function dispatch(int $id);
}
