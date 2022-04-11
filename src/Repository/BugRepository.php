<?php

namespace Tienvx\Bundle\MbtBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class BugRepository extends ServiceEntityRepository implements BugRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bug::class);
    }

    public function updateSteps(BugInterface $bug, array $newSteps): void
    {
        $this->getEntityManager()->wrapInTransaction(function () use ($bug, $newSteps): void {
            // Refresh the bug for the latest steps's length.
            $this->getEntityManager()->refresh($bug);

            if (count($newSteps) <= count($bug->getSteps())) {
                $this->getEntityManager()->lock($bug, LockMode::PESSIMISTIC_WRITE);
                $bug->getProgress()->setTotal(0);
                $bug->getProgress()->setProcessed(0);
                $bug->setSteps($newSteps);
            }
        });
    }

    public function increaseProcessed(BugInterface $bug, int $processed = 1): void
    {
        $this->getEntityManager()->wrapInTransaction(function () use ($bug, $processed): void {
            // Refresh the bug for the latest progress.
            $this->getEntityManager()->refresh($bug);

            $this->getEntityManager()->lock($bug, LockMode::PESSIMISTIC_WRITE);
            $bug->getProgress()->increase($processed);
        });
    }

    public function increaseTotal(BugInterface $bug, int $total): void
    {
        $this->getEntityManager()->wrapInTransaction(function () use ($bug, $total): void {
            // Refresh the bug for the latest progress.
            $this->getEntityManager()->refresh($bug);

            $this->getEntityManager()->lock($bug, LockMode::PESSIMISTIC_WRITE);
            $bug->getProgress()->setTotal($bug->getProgress()->getTotal() + $total);
        });
    }

    public function startRecording(BugInterface $bug): void
    {
        $bug->setRecording(true);
        $this->getEntityManager()->flush();
    }

    public function stopRecording(BugInterface $bug): void
    {
        $bug->setRecording(false);
        // Recording bug may take long time. Reconnect to flush changes.
        $this->getEntityManager()->getConnection()->connect();
        $this->getEntityManager()->flush();
    }
}
