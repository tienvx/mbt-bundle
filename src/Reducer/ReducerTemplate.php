<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

abstract class ReducerTemplate implements ReducerInterface
{
    protected DispatcherInterface $dispatcher;

    protected HandlerInterface $handler;

    public static function getManager(): string
    {
        return ReducerManager::class;
    }

    public static function isSupported(): bool
    {
        return true;
    }

    public function dispatch(BugInterface $bug): int
    {
        return $this->dispatcher->dispatch($bug);
    }

    public function handle(BugInterface $bug, int $from, int $to): void
    {
        $this->handler->handle($bug, $from, $to);
    }
}
