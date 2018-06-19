<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
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

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        PathRunner $runner,
        EventDispatcherInterface $dispatcher,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager)
    {
        $this->runner = $runner;
        $this->dispatcher = $dispatcher;
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder  = $graphBuilder;
        $this->entityManager = $entityManager;
    }

    protected function finish(int $reproducePathId)
    {
        $event = new ReducerFinishEvent($reproducePathId);

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);
    }

    /**
     * @param ReproducePath $reproducePath
     * @param string $steps
     * @param int $length
     * @throws \Exception
     */
    protected function updateSteps(ReproducePath $reproducePath, string $steps, int $length)
    {
        $reproducePath->setSteps($steps);
        $reproducePath->setLength($length);
        $this->entityManager->flush();
    }

    public function handle(string $message)
    {
    }
}
