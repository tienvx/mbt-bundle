<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;

class ReducerSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $reporterManager;
    private $defaultReporter;

    public function __construct(EntityManagerInterface $entityManager, ReporterManager $reporterManager)
    {
        $this->entityManager = $entityManager;
        $this->reporterManager = $reporterManager;
    }

    public function setDefaultReporter(string $defaultReporter)
    {
        $this->defaultReporter = $defaultReporter;
    }

    public function onFinish(ReducerFinishEvent $event)
    {
        if (!$this->reporterManager->hasReporter($this->defaultReporter)) {
            return;
        }

        $taskId = $event->getTaskId();
        if (!$taskId) {
            return;
        }

        $task = $this->entityManager->getRepository(Task::class)->find($taskId);
        if (!$task || !$task instanceof Task) {
            return;
        }

        $bug = new Bug();
        $bug->setTitle($event->getBugMessage());
        $bug->setMessage($event->getBugMessage());
        $bug->setTask($task);
        $bug->setSteps($event->getPath());
        $bug->setStatus('unverified');
        $bug->setReporter($this->defaultReporter);
        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.reducer.finish' => 'onFinish',
        ];
    }
}
