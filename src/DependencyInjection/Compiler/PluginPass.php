<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManager;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterManager;

class PluginPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tags = [
            'mbt.generator' => GeneratorManager::class,
            'mbt.reducer' => ReducerManager::class,
            'mbt.reporter' => ReporterManager::class,
        ];
        foreach ($tags as $tag => $managerClass) {
            $this->buildPlugins($container, $tag, $managerClass);
        }
    }

    protected function buildPlugins(ContainerBuilder $container, string $tag, string $managerClass): void
    {
        $plugins = [];
        foreach ($container->findTaggedServiceIds($tag, true) as $serviceId => $attributes) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();
            $support = call_user_func([$class, 'support']);
            if ($support) {
                $name = call_user_func([$class, 'getName']);
                $plugins[$name] = new Reference($serviceId);
            }
        }

        $managerDefinition = $container->getDefinition($managerClass);
        $managerDefinition->setBindings(['array $plugins' => $plugins]);
    }
}
