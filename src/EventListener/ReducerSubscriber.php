<?php

namespace Tienvx\Bundle\MbtBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Service\ReporterManager;

class ReducerSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $reporterManager;
    private $defaultReporter;
    private $defaultBugTitle;

    public function __construct(EntityManagerInterface $entityManager, ReporterManager $reporterManager)
    {
        $this->entityManager = $entityManager;
        $this->reporterManager = $reporterManager;
    }

    public function setDefaultReporter(string $defaultReporter)
    {
        $this->defaultReporter = $defaultReporter;
    }

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }

    public function onFinish(ReducerFinishEvent $event)
    {
        if (!$this->reporterManager->hasReporter($this->defaultReporter)) {
            return;
        }

        $reproducePathId = $event->getReproducePathId();
        $reproducePath = $this->entityManager->getRepository(ReproducePath::class)->find($reproducePathId);
        if (!$reproducePath || !$reproducePath instanceof ReproducePath) {
            return;
        }

        $bug = new Bug();
        $bug->setTitle($this->defaultBugTitle);
        $bug->setReproducePath($reproducePath);
        $bug->setStatus('unverified');
        $bug->setReporter($this->defaultReporter);
        $this->entityManager->persist($bug);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'tienvx_mbt.finish_reduce' => 'onFinish',
        ];
    }
}
