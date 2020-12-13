<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

interface BugHelperInterface
{
    public function setAdminUrl(string $adminUrl): void;

    public function create(array $steps, string $message, TaskInterface $task): BugInterface;

    public function buildBugUrl(BugInterface $bug): string;
}
