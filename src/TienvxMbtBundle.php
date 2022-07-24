<?php

namespace Tienvx\Bundle\MbtBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Tienvx\Bundle\MbtBundle\Command\CommandManagerInterface;
use Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler\PluginPass;
use Tienvx\Bundle\MbtBundle\Service\SelenoidHelperInterface;

class TienvxMbtBundle extends AbstractBundle
{
    public const WEBDRIVER_URI = 'webdriver_uri';
    public const UPLOAD_DIR = 'upload_dir';

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new PluginPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->services()
            ->get(SelenoidHelperInterface::class)
            ->call('setWebdriverUri', [$config[static::WEBDRIVER_URI]])
        ;
        $container->services()
            ->get(CommandManagerInterface::class)
            ->call('setUploadDir', [$config[static::UPLOAD_DIR]])
        ;
    }
}
