<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\GuardPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\SecurityPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\SubjectPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GuardPass());
        $container->addCompilerPass(new PluginPass());
        $container->addCompilerPass(new SecurityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new SubjectPass());
    }
}
