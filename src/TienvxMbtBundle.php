<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\GeneratorPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\ModelPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PathReducerPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\StopConditionPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ModelPass());
        $container->addCompilerPass(new GeneratorPass());
        $container->addCompilerPass(new StopConditionPass());
        $container->addCompilerPass(new PathReducerPass());
    }
}
