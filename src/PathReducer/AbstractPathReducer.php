<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Service\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;
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

    /**
     * @var ModelRegistry
     */
    protected $modelRegistry;

    /**
     * @var GraphBuilder
     */
    protected $graphBuilder;

    public function __construct(
        PathRunner $runner,
        EventDispatcherInterface $dispatcher,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder)
    {
        $this->runner = $runner;
        $this->dispatcher = $dispatcher;
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder  = $graphBuilder;
    }

    public function finish(int $reproducePathId)
    {
        $event = new ReducerFinishEvent($reproducePathId);

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);
    }

    public function handle(string $message)
    {
    }
}
