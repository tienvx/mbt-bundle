<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Entity\Bug;

abstract class ReducerTemplate implements ReducerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var HandlerInterface
     */
    protected $handler;

    public function dispatch(Bug $bug): int
    {
        return $this->dispatcher->dispatch($bug);
    }

    public function handle(Bug $bug, int $length, int $from, int $to): void
    {
        $this->handler->handle($bug, $length, $from, $to);
    }
}
