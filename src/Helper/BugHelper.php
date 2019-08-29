<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\LockMode;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Steps;

class BugHelper
{
    /**
     * @param EntityManager $entityManager
     * @param Bug           $bug
     * @param Steps         $newSteps
     *
     * @throws Throwable
     */
    public static function updateSteps(EntityManager $entityManager, Bug $bug, Steps $newSteps)
    {
        $length = $bug->getSteps()->getLength();
        $callback = function () use ($entityManager, $bug, $newSteps, $length) {
            // Reload the bug for the newest messages length.
            $bug = $entityManager->find(Bug::class, $bug->getId(), LockMode::PESSIMISTIC_WRITE);

            if ($bug instanceof Bug && $length === $bug->getSteps()->getLength()) {
                $bug->setSteps($newSteps);
            }
        };

        $entityManager->transactional($callback);
    }
}
