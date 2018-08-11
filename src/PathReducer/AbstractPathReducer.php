<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Graph\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Model\ModelRegistry;

abstract class AbstractPathReducer implements PathReducerInterface
{
    use NewPathTrait;

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
        EventDispatcherInterface $dispatcher,
        ModelRegistry $modelRegistry,
        GraphBuilder $graphBuilder,
        EntityManagerInterface $entityManager)
    {
        $this->dispatcher = $dispatcher;
        $this->modelRegistry = $modelRegistry;
        $this->graphBuilder  = $graphBuilder;
        $this->entityManager = $entityManager;
    }

    protected function finish(int $bugId)
    {
        $event = new ReducerFinishEvent($bugId);

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);
    }

    /**
     * @param Bug $bug
     * @param string $steps
     * @param int $length
     * @throws \Exception
     */
    protected function updateSteps(Bug $bug, string $steps, int $length)
    {
        $bug->setSteps($steps);
        $bug->setLength($length);
        $this->entityManager->flush();
    }

    public function handle(string $message)
    {
    }
}
