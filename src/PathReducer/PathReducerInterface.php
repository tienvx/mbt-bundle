<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\ReproducePath;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(ReproducePath $reproducePath);

    public function handle(string $message);
}
