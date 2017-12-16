<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Workflow;
use Tienvx\Bundle\MbtBundle\EventListener\ExpressionListener;
use Tienvx\Bundle\MbtBundle\Model;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TienvxMbtExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->registerModelConfiguration($config['models'], $container);
    }

    /**
     * Loads the model configuration.
     *
     * @param array            $models A model configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    private function registerModelConfiguration(array $models, ContainerBuilder $container)
    {
        if (!$models) {
            return;
        }

        if (!class_exists(Workflow\StateMachine::class)) {
            throw new LogicException('Model support cannot be enabled as the Workflow component is not installed.');
        }

        if ($container->hasDefinition('workflow.registry')) {
            $registryDefinition = $container->getDefinition('workflow.registry');
        }
        else {
            // No workflows have been defined. Create service definition manually.
            $registryDefinition = new Definition(Workflow\Registry::class);
            $container->setDefinition('workflow.registry', $registryDefinition);
        }

        foreach ($models as $name => $model) {
            $type = 'state_machine';

            $transitions = [];
            foreach ($model['transitions'] as $transition) {
                $transitions[] = new Definition(Model\Transition::class, [$transition['name'], $transition['from'], $transition['to'], $transition['weight'], $transition['label']]);
            }

            // Create a Definition
            $definitionDefinition = new Definition(Workflow\Definition::class);
            $definitionDefinition->setPublic(false);
            $definitionDefinition->addArgument($model['places']);
            $definitionDefinition->addArgument($transitions);
            $definitionDefinition->setTags([
                'workflow.definition' => [
                    'name' => $name,
                    'type' => $type,
                    'marking_store' => null,
                ],
                'model.definition' => [
                    'name' => $name,
                ]
            ]);
            if (isset($model['initial_place'])) {
                $definitionDefinition->addArgument($model['initial_place']);
            }

            // Create Model
            if (!isset($model['subject']) || !class_exists($model['subject'])) {
                throw new RuntimeException(sprintf('Subject "%s" must be an existing class.', $model['subject']));
            }
            $modelDefinition = new Definition(Model\Model::class);
            $modelDefinition->addArgument($definitionDefinition);
            $modelDefinition->addArgument($model['subject']);
            $modelDefinition->addArgument(new Reference('event_dispatcher'));
            $modelDefinition->addArgument($name);
            $modelDefinition->addArgument($model['label']);
            foreach ($model['tags'] as $tag) {
                $modelDefinition->addTag($tag);
            }

            // Store to container
            $modelId = sprintf('model.%s', $name);
            $container->setDefinition($modelId, $modelDefinition);
            $container->setDefinition(sprintf('%s.definition', $modelId), $definitionDefinition);

            // Add workflow to Registry
            $strategyDefinition = new Definition(Workflow\SupportStrategy\ClassInstanceSupportStrategy::class, [$model['subject']]);
            $strategyDefinition->setPublic(false);
            $registryDefinition->addMethodCall('add', [new Reference($modelId), $strategyDefinition]);

            // Add Guard Listener
            $listener = new Definition(ExpressionListener::class);
            $guardConfiguration = [];
            $dataConfiguration = [];
            foreach ($model['transitions'] as $transitionName => $config) {
                if (!isset($config['guard']) && !isset($config['data'])) {
                    continue;
                }

                if (isset($config['guard'])) {
                    $guardEventName = sprintf('workflow.%s.guard.%s', $name, $transitionName);
                    $listener->addTag('kernel.event_listener', ['event' => $guardEventName, 'method' => 'onGuard']);
                    $guardConfiguration[$guardEventName] = $config['guard'];
                }

                if (isset($config['data'])) {
                    $dataConfiguration[$name][$transitionName] = $config['data'];
                }
            }
            if ($guardConfiguration) {
                $listener->setArguments([
                    $guardConfiguration,
                    new Reference('tienvx_mbt.expression_language'),
                ]);

                $container->setDefinition(sprintf('%s.listener.expression', $modelId), $listener);
            }
            if ($dataConfiguration) {
                $dataProviderDefinition = $container->getDefinition('tienvx_mbt.data_provider');
                $dataProviderDefinition->setArguments([
                    $dataConfiguration,
                    new Reference('tienvx_mbt.expression_language'),
                ]);
            }
        }
    }
}
