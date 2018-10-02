<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\GeneratorPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PathReducerPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\ReporterPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\SecurityTokenPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\WorkflowRegistryPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GeneratorPass());
        $container->addCompilerPass(new PathReducerPass());
        $container->addCompilerPass(new ReporterPass());
        $container->addCompilerPass(new SecurityTokenPass());
        $container->addCompilerPass(new WorkflowRegistryPass());
    }
}
