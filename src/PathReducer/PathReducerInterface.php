<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Throwable;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Graph\Path;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(Path $path, string $model, string $subject, Throwable $throwable): Path;
}
