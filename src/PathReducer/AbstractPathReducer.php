<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\PathRunner;

abstract class AbstractPathReducer implements PathReducerInterface
{
    use NewPathTrait;

    /**
     * @var PathRunner
     */
    protected $runner;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(PathRunner $runner, EventDispatcherInterface $dispatcher)
    {
        $this->runner = $runner;
        $this->dispatcher = $dispatcher;
    }

    public function finish(string $bugMessage, Path $path, $taskId = null)
    {
        $event = new ReducerFinishEvent($bugMessage, $path, $taskId);

        $this->dispatcher->dispatch('tienvx_mbt.reducer.finish', $event);
    }
}
