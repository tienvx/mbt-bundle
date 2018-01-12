<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddModelResourcesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $paths = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $name => $bundle) {
            $paths[] = $bundle['path'] . '/Resources/config/models';
        }
        $paths[] = $container->getParameter('kernel.root_dir') . '/config/models';
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $container->addResource(new DirectoryResource($path, '/\.(xml|ya?ml|php)$/'));
            }
        }
    }
}
