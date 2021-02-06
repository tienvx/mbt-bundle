<?php

namespace Tienvx\Bundle\MbtBundle\Service\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface BugNotifierInterface
{
    public function notify(BugInterface $bug): void;
}
