<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface BugHelperInterface
{
    public function reduceBug(int $bugId): void;

    public function reduceSteps(int $bugId, int $length, int $from, int $to): void;

    public function reportBug(int $bugId): void;

    public function recordVideo(int $bugId): void;

    public function createBug(array $steps, string $message): BugInterface;
}
