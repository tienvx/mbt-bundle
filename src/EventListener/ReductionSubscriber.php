<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Helper\CommandRunner;

class ReductionSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * @param ReducerFinishEvent $event
     * @throws \Exception
     */
    public function onFinish(ReducerFinishEvent $event)
    {
        $id = $event->getBugId();
        CommandRunner::run($this->kernel, sprintf('mbt:report-bug %d', $id));
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.finish_reduce' => 'onFinish',
        ];
    }
}
