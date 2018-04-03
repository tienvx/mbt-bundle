<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use InvalidArgumentException;

trait TaggedServiceTrait
{
    private function findTaggedServices(string $tagName, string $interface, ContainerBuilder $container)
    {
        $services = [];
        foreach ($container->findTaggedServiceIds($tagName, true) as $serviceId => $attributes) {
            $def = $container->getDefinition($serviceId);

            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $def->getClass();

            if (!$r = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $serviceId));
            }
            if (!method_exists($interface, 'getName')) {
                throw new InvalidArgumentException(sprintf('Interface "%s" must has method "%s".', $interface, 'getName'));
            }
            if (!$r->isSubclassOf($interface)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $serviceId, $interface));
            }
            $class = $r->name;

            $serviceName = call_user_func([$class, 'getName']);
            $services[$serviceName] = new Reference($serviceId);
        }
        return $services;
    }
}
