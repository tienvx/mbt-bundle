<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Symfony\Component\Workflow\Workflow;
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

    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to): void
    {
        $this->handler->handle($bug, $workflow, $length, $from, $to);
    }
}
