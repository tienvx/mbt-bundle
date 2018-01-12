<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\AddGeneratorDirectoriesCompilerPass;
//use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\AddModelResourcesCompilerPass;

class TienvxMbtBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddGeneratorDirectoriesCompilerPass());
        //$container->addCompilerPass(new AddModelResourcesCompilerPass());

        // Add these code in a compiler pass is too late.
        $paths = [];
        foreach ($container->getParameter('kernel.bundles_metadata') as $name => $bundle) {
            $paths[] = $bundle['path'] . '/Resources/config/models';
        }
        $paths[] = $container->getParameter('kernel.root_dir') . '/config/models';
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $path = $container->getParameterBag()->resolveValue($path);
                $loader = new YamlFileLoader($container, new FileLocator($path));
                foreach (Finder::create()->followLinks()->files()->in($path)->name('/\.(ya?ml)$/') as $file) {
                    $loader->load($file->getRealPath());
                }
            }
        }
        /*foreach ($paths as $path) {
            if (is_dir($path)) {
                $container->addResource(new DirectoryResource($path, '/\.(xml|ya?ml|php)$/'));
            }
        }*/
    }
}
