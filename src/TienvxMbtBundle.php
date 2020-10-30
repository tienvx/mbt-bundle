<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PluginPass());
    }
}
