<?php

namespace Tienvx\Bundle\MbtBundle\Service\Model;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;

interface ModelDumperInterface
{
    public function dump(ModelInterface $model): string;
}
