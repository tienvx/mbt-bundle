<?php

namespace Tienvx\Bundle\MbtBundle\Service\Step\Builder;

use Generator;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;

interface StepsBuilderInterface
{
    public function create(BugInterface $bug, int $from, int $to): Generator;
}
