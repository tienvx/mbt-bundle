<?php

namespace Tienvx\Bundle\MbtBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface BugRepositoryInterface extends ObjectRepository
{
    public function updateSteps(BugInterface $bug, array $newSteps): void;

    public function increaseProcessed(BugInterface $bug, int $processed = 1): void;

    public function increaseTotal(BugInterface $bug, int $total): void;

    public function startRecording(BugInterface $bug): void;

    public function stopRecording(BugInterface $bug): void;

    public function updateVideoErrorMessage(BugInterface $bug, ?string $errorMessage): void;
}
