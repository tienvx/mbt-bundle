<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\MessageHandler\CaptureScreenshotsMessageHandler;
use Tienvx\Bundle\MbtBundle\MessageHandler\RemoveScreenshotsMessageHandler;

class StoragePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $services = [
            CaptureScreenshotsMessageHandler::class,
            RemoveScreenshotsMessageHandler::class,
        ];
        if ($container->has('mbt.storage')) {
            foreach ($services as $service) {
                $container->getDefinition($service)->addMethodCall('setMbtStorage', [new Reference('mbt.storage')]);
            }
        }
    }
}
