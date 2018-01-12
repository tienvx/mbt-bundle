<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\Service\GeneratorDiscovery;

class AddGeneratorDirectoriesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $dirs = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $name => $bundle) {
            $dir = $bundle['path'] . '/Generator';
            if (file_exists($dir)) {
                $dirs[] = $dir;
            }
        }
        $dir = $container->getParameter('kernel.root_dir') . '/Generator';
        if (file_exists($dir)) {
            $dirs[] = $dir;
        }
        $discoveryDefinition = $container->findDefinition(GeneratorDiscovery::class);
        $discoveryDefinition->setArgument(3, $dirs);
    }
}
