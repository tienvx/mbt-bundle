<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;

class ReducerSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $reporterManager;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, ReporterManager $reporterManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->reporterManager = $reporterManager;
        $this->params = $params;
    }

    public function onFinish(ReducerFinishEvent $event)
    {
        $id = $event->getBugId();
        $process = new Process("bin/console mbt:report-bug $id");
        $process->setTimeout(null);
        $process->setWorkingDirectory($this->params->get('kernel.project_dir'));

        $process->run();
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.finish_reduce' => 'onFinish',
        ];
    }
}
