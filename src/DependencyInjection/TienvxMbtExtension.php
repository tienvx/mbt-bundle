<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Swift_Mailer;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerInterface;
use Tienvx\Bundle\MbtBundle\Reporter\EmailReporter;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;
use Tienvx\Bundle\MbtBundle\StopCondition\CoverageStopCondition;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;
use Twig\Environment as Twig;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $emailReporterDefinition = $container->getDefinition(EmailReporter::class);
        $emailReporterDefinition->addMethodCall('setFrom', [$config['reporter']['email']['from']]);
        $emailReporterDefinition->addMethodCall('setTo', [$config['reporter']['email']['to']]);
        if (class_exists(Swift_Mailer::class)) {
            $emailReporterDefinition->addMethodCall('setMailer', [new Reference(Swift_Mailer::class)]);
        }
        if (class_exists(Twig::class)) {
            $emailReporterDefinition->addMethodCall('setTwig', [new Reference(Twig::class)]);
        }

        $executeTaskCommandDefinition = $container->getDefinition(ExecuteTaskCommand::class);
        $executeTaskCommandDefinition->addMethodCall('setDefaultReporter', [$config['default_reporter']]);

        $coverageStopConditionDefinition = $container->getDefinition(CoverageStopCondition::class);
        $coverageStopConditionDefinition->addMethodCall('setMaxPathLength', [$config['max_path_length']]);

        $container->registerForAutoconfiguration(GeneratorInterface::class)
            ->setLazy(true)
            ->addTag('mbt.generator');
        $container->registerForAutoconfiguration(StopConditionInterface::class)
            ->setLazy(true)
            ->addTag('mbt.stop_condition');
        $container->registerForAutoconfiguration(PathReducerInterface::class)
            ->setLazy(true)
            ->addTag('mbt.path_reducer');
        $container->registerForAutoconfiguration(ReporterInterface::class)
            ->setLazy(true)
            ->addTag('mbt.reporter');
    }
}
