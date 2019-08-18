<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\GeneratorPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\ReducerPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\ReporterPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\SecurityTokenPass;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\SubjectPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GeneratorPass());
        $container->addCompilerPass(new ReducerPass());
        $container->addCompilerPass(new SecurityTokenPass());
        $container->addCompilerPass(new SubjectPass());
        $container->addCompilerPass(new ReporterPass());
    }
}
