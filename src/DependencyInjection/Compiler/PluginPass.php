<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginManagerInterface;

class PluginPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $allPlugins = [];
        foreach (array_keys($container->findTaggedServiceIds(PluginInterface::TAG, true)) as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();
            if (is_subclass_of($class, PluginInterface::class) && $class::isSupported()) {
                $allPlugins[$class::getManager()][$class::getName()] = new Reference($serviceId);
            }
        }

        foreach ($allPlugins as $manager => $plugins) {
            if (is_subclass_of($manager, PluginManagerInterface::class)) {
                $managerDefinition = $container->getDefinition($manager);
                $managerDefinition->setArguments([
                    ServiceLocatorTagPass::register($container, $plugins),
                    array_keys($plugins),
                ]);
            }
        }
    }
}
