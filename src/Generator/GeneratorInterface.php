<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface GeneratorInterface extends PluginInterface
{
    public function generate(TaskInterface $task): iterable;
}
