<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;

interface PathReducerInterface extends PluginInterface
{
    public function reduce(Bug $bug);

    public function handle(string $message);
}
