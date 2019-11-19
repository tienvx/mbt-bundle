<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Steps\Steps;

class BugHelper
{
    /**
     * @var string
     */
    private $defaultBugTitle;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setDefaultBugTitle(string $defaultBugTitle)
    {
        $this->defaultBugTitle = $defaultBugTitle;
    }

    public function getDefaultBugTitle(): string
    {
        return $this->defaultBugTitle;
    }

    /**
     * @throws Throwable
     */
    public function updateSteps(Bug $bug, Steps $newSteps)
    {
        $length = $bug->getSteps()->getLength();
        $callback = function () use ($bug, $newSteps, $length) {
            // Reload the bug for the newest messages length.
            $bug = $this->entityManager->find(Bug::class, $bug->getId(), LockMode::PESSIMISTIC_WRITE);

            if ($bug instanceof Bug && $length === $bug->getSteps()->getLength()) {
                $bug->setSteps($newSteps);
            }
        };

        $this->entityManager->transactional($callback);
    }
}
