<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface BugHelperInterface
{
    public function createBug(array $steps, string $message): BugInterface;
}
