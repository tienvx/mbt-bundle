<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface NotifyHelperInterface
{
    public function notify(BugInterface $bug): void;
}
