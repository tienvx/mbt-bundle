<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MailTransportPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('tienvx_mbt.model_container')) {
            return;
        }

        $definition = $container->findDefinition('tienvx_mbt.model_container');

        $taggedServices = $container->findTaggedServiceIds('model.definition');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addModel', array(new Reference($id)));
        }
    }
}
