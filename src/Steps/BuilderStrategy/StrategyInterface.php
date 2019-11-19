<?php

namespace Tienvx\Bundle\MbtBundle\Steps\BuilderStrategy;

use Tienvx\Bundle\MbtBundle\Steps\Steps;

interface StrategyInterface
{
    public function create(Steps $original, int $from, int $to): Steps;
}
