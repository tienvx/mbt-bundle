<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\AddGeneratorDirectoriesCompilerPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddGeneratorDirectoriesCompilerPass());

        // Read models configurations. Add these code in a compiler pass is too late.
        $paths = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $name => $bundle) {
            $path = $bundle['path'] . '/Resources/config/models';
            if (is_dir($path)) {
                $paths[] = $path;
            }
        }
        $path = $container->getParameter('kernel.project_dir') . '/config/models';
        if (is_dir($path)) {
            $paths[] = $path;
        }
        if ($paths) {
            $loader = new YamlFileLoader($container, new FileLocator($paths));
            foreach (Finder::create()->followLinks()->files()->in($paths)->name('/\.(ya?ml)$/') as $file) {
                $loader->load($file->getRelativePathname());
            }
        }
    }
}
