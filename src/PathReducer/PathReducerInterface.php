<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;
use Tienvx\Bundle\MbtBundle\Model\Model;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(Path $path, Model $model, string $bugMessage, $taskId = null);
}
