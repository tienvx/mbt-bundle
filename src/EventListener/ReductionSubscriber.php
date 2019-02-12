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
    public function reportBug(ReducerFinishEvent $event)
    {
        $bug = $event->getBug();
        $this->commandRunner->run(['mbt:bug:report', $bug->getId()]);
    }

    /**
     * @param ReducerFinishEvent $event
     * @throws \Exception
     */
    public function captureScreenshots(ReducerFinishEvent $event)
    {
        $bug = $event->getBug();
        if ($bug->getTask()->getTakeScreenshots()) {
            $this->commandRunner->run(['mbt:bug:capture-screenshots', $bug->getId()]);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.finish_reduce' => [
                ['captureScreenshots'],
                ['reportBug'],
            ],
        ];
    }
}
