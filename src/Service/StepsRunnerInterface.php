<?php

namespace Tienvx\Bundle\MbtBundle\Service;

interface StepsRunnerInterface
{
    public function run(iterable $steps): iterable;
}
