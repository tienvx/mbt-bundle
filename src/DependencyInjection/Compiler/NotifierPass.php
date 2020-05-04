<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\MessageHandler\ReportBugMessageHandler;

class NotifierPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('Symfony\Component\Notifier\NotifierInterface')) {
            $container->getDefinition(ReportBugMessageHandler::class)->addMethodCall('setNotifier', [new Reference('Symfony\Component\Notifier\NotifierInterface')]);
        }
    }
}
