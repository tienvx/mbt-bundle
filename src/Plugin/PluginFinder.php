<?php

namespace Tienvx\Bundle\MbtBundle\Plugin;

use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PluginFinder
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    public function find(string $tagName): array
    {
        $services = [];
        foreach ($this->container->findTaggedServiceIds($tagName, true) as $serviceId => $attributes) {
            $definition = $this->container->getDefinition($serviceId);
            $class = $this->getClass($definition, $serviceId);

            $support = call_user_func([$class, 'support']);
            if ($support) {
                $serviceName = call_user_func([$class, 'getName']);
                $services[$serviceName] = new Reference($serviceId);
            }
        }

        return $services;
    }

    protected function getClass(Definition $definition, string $serviceId): string
    {
        // We must assume that the class value has been correctly filled, even if the service is created by a factory
        $class = $definition->getClass();
        $reflection = $this->container->getReflectionClass($class);

        if (!$reflection) {
            throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $serviceId));
        }
        if (!$reflection->isSubclassOf(PluginInterface::class)) {
            throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $serviceId, PluginInterface::class));
        }

        return $reflection->name;
    }
}
