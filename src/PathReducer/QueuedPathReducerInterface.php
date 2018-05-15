<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Messenger\Message\QueuedPathReducerMessage;

interface QueuedPathReducerInterface
{
    public function handle(QueuedPathReducerMessage $queuedPathReducerMessage);

    public function dispatch(ReproducePath $pathEntity);
}
