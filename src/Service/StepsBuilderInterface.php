<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface StepsBuilderInterface
{
    public function create(BugInterface $bug, int $from, int $to): StepsInterface;
}
