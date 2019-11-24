<?php

namespace Tienvx\Bundle\MbtBundle\Graph\BuilderStrategy;

use Fhaculty\Graph\Graph;

interface StrategyInterface
{
    public function build(): Graph;
}
