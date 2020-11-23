<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface GeneratorInterface extends PluginInterface
{
    public function generate(ModelInterface $model): iterable;
}
