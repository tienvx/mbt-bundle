<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Workflow\StateMachine;
use Tienvx\Bundle\MbtBundle\EventListener\ModelGuardListener;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorInterface;
use Tienvx\Bundle\MbtBundle\PathReducer\PathReducerInterface;
use Tienvx\Bundle\MbtBundle\Reporter\EmailReporter;
use Tienvx\Bundle\MbtBundle\Reporter\ReporterInterface;
use Tienvx\Bundle\MbtBundle\StopCondition\StopConditionInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if (!$frameworkConfiguration = $container->getExtensionConfig('framework')) {
            return;
        }

        foreach ($frameworkConfiguration as $frameworkParameters) {
            if (isset($frameworkParameters['workflows'])) {
                $this->registerWorkflowConfiguration($frameworkParameters, $container);
            }
        }
    }

    /**
     * {@inheritdoc}
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

    private function registerWorkflowConfiguration(array $config, ContainerBuilder $container)
    {
        if (!class_exists(StateMachine::class)) {
            throw new LogicException('Model support cannot be enabled as the Workflow component is not installed.');
        }

        foreach ($config['workflows'] as $name => $workflow) {
            $type = $workflow['type'];
            if ($type !== 'state_machine') {
                continue;
            }

            // Add Guard Listener
            $guard = new Definition(ModelGuardListener::class);
            $guard->setPrivate(true);
            $configuration = array();
            foreach ($workflow['transitions'] as $transitionName => $config) {
                if (!isset($config['metadata']['model_guard'])) {
                    continue;
                }

                if (!class_exists(ExpressionLanguage::class)) {
                    throw new LogicException('Cannot guard models as the ExpressionLanguage component is not installed.');
                }

                $eventName = sprintf('workflow.%s.guard.%s', $name, $transitionName);
                $guard->addTag('kernel.event_listener', array('event' => $eventName, 'method' => 'onTransition'));
                $configuration[$eventName] = $config['metadata']['model_guard'];
            }
            if ($configuration) {
                $guard->setArguments(array(
                    $configuration,
                    new Reference(ExpressionLanguage::class),
                ));

                $workflowId = sprintf('%s.%s', $type, $name);
                $container->setDefinition(sprintf('%s.listener.model_guard', $workflowId), $guard);
            }
        }
    }
}
