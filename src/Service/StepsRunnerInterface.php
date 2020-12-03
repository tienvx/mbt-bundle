<?php

namespace Tienvx\Bundle\MbtBundle\Service;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface StepsRunnerInterface
{
    public function run(iterable $steps, ModelInterface $model): iterable;
}
