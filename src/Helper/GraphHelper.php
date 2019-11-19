<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Graph;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy\StateMachineStrategy;
use Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy\StrategyInterface;
use Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy\WorkflowStrategy;
use Tienvx\Bundle\MbtBundle\Graph\GraphBuilder;

class GraphHelper
{
    /**
     * @var AdapterInterface
     */
    protected $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function build(Workflow $workflow): Graph
    {
        $cacheItem = $this->cache->getItem('mbt.graph.'.$workflow->getName());
        if ($cacheItem->isHit() && ($graph = $cacheItem->get()) instanceof Graph) {
            return $graph;
        }

        $builder = new GraphBuilder();
        $builder->setStrategy($this->getStrategy($workflow));
        $graph = $builder->build();

        $cacheItem->set($graph);
        $this->cache->save($cacheItem);

        return $graph;
    }

    protected function getStrategy(Workflow $workflow): StrategyInterface
    {
        return $workflow instanceof StateMachine ? new StateMachineStrategy($workflow) : new WorkflowStrategy($workflow);
    }
}
