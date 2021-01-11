<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tienvx\Bundle\MbtBundle\Command\CommandRunnerInterface;
use Tienvx\Bundle\MbtBundle\MessageHandler\ExecuteTaskMessageHandler;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->findDefinition(ProviderManager::class)
            ->addMethodCall('setConfig', [$config[Configuration::PROVIDERS]])
        ;

        $container->findDefinition(ExecuteTaskMessageHandler::class)
            ->addMethodCall('setMaxSteps', [$config[Configuration::MAX_STEPS]])
        ;

        $this->registerForAutoconfiguration($container);
    }

    private function registerForAutoconfiguration(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(PluginInterface::class)
            ->setLazy(true)
            ->addTag(PluginInterface::TAG);
        $container->registerForAutoconfiguration(CommandRunnerInterface::class)
            ->setLazy(true)
            ->addTag(CommandRunnerInterface::TAG);
    }
}
