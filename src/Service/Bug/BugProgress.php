<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class BugProgress implements BugProgressInterface
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function increaseProcessed(BugInterface $bug, int $processed = 1): void
    {
        $callback = function () use ($bug, $processed): void {
            // Refresh the bug for the latest progress.
            $this->entityManager->refresh($bug);

            $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);
            $bug->getProgress()->increase($processed);
        };

        $this->entityManager->transactional($callback);
    }

    public function increaseTotal(BugInterface $bug, int $total): void
    {
        $callback = function () use ($bug, $total): void {
            // Refresh the bug for the latest progress.
            $this->entityManager->refresh($bug);

            $this->entityManager->lock($bug, LockMode::PESSIMISTIC_WRITE);
            $bug->getProgress()->setTotal($bug->getProgress()->getTotal() + $total);
        };

        $this->entityManager->transactional($callback);
    }
}
