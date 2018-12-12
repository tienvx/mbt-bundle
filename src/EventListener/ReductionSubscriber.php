<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Command\CommandRunner;

class ReductionSubscriber implements EventSubscriberInterface
{
    /**
     * @var CommandRunner
     */
    private $commandRunner;

    public function __construct(CommandRunner $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * @param ReducerFinishEvent $event
     * @throws \Exception
     */
    public function onFinish(ReducerFinishEvent $event)
    {
        $id = $event->getBugId();
        $this->commandRunner->run(sprintf('mbt:bug:report %d', $id));
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.finish_reduce' => 'onFinish',
        ];
    }
}
