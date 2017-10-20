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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow;
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

            $transitions = array();
            foreach ($model['transitions'] as $transition) {
                foreach ($transition['from'] as $from) {
                    foreach ($transition['to'] as $to) {
                        $transitions[] = new Definition(Model\Transition::class, array($transition['name'], $from, $to, $transition['weight']));
                    }
                }
            }

            // Create a Definition
            $definitionDefinition = new Definition(Workflow\Definition::class);
            $definitionDefinition->setPublic(false);
            $definitionDefinition->addArgument($model['places']);
            $definitionDefinition->addArgument($transitions);
            $definitionDefinition->addTag('workflow.definition', array(
                'name' => $name,
                'type' => $type,
                'marking_store' => null,
            ));
            if (isset($model['initial_place'])) {
                $definitionDefinition->addArgument($model['initial_place']);
            }

            // Create Model
            if (!isset($model['instance']) || !class_exists($model['instance'])) {
                throw new RuntimeException(sprintf('Instance "%s" must be an existing class.', $model['instance']));
            }
            $modelDefinition = new Definition(Model\Model::class);
            $modelDefinition->addArgument($definitionDefinition);
            $modelDefinition->addArgument($model['instance']);
            $modelDefinition->addArgument(new Reference('event_dispatcher'));
            $modelDefinition->addArgument($name);
            foreach ($model['tags'] as $tag) {
                $modelDefinition->addTag($tag);
            }

            // Store to container
            $modelId = sprintf('model.%s', $name);
            $container->setDefinition($modelId, $modelDefinition);
            $container->setDefinition(sprintf('%s.definition', $modelId), $definitionDefinition);

            // Add workflow to Registry
            $strategyDefinition = new Definition(Workflow\SupportStrategy\ClassInstanceSupportStrategy::class, array($model['instance']));
            $strategyDefinition->setPublic(false);
            $registryDefinition->addMethodCall('add', array(new Reference($modelId), $strategyDefinition));

            // Add Guard Listener
            $guard = new Definition(Workflow\EventListener\GuardListener::class);
            $configuration = array();
            foreach ($model['transitions'] as $transitionName => $config) {
                if (!isset($config['guard'])) {
                    continue;
                }

                if (!class_exists(ExpressionLanguage::class)) {
                    throw new LogicException('Cannot guard models as the ExpressionLanguage component is not installed.');
                }

                if (!class_exists(Security::class)) {
                    throw new LogicException('Cannot guard workflows as the Security component is not installed.');
                }

                $eventName = sprintf('workflow.%s.guard.%s', $name, $transitionName);
                $guard->addTag('kernel.event_listener', array('event' => $eventName, 'method' => 'onTransition'));
                $configuration[$eventName] = $config['guard'];
            }
            if ($configuration) {
                $guard->setArguments(array(
                    $configuration,
                    new Reference('workflow.security.expression_language'),
                    new Reference('security.token_storage'),
                    new Reference('security.authorization_checker'),
                    new Reference('security.authentication.trust_resolver'),
                    new Reference('security.role_hierarchy'),
                ));

                $container->setDefinition(sprintf('%s.listener.guard', $modelId), $guard);
                $container->setParameter('workflow.has_guard_listeners', true);
            }
        }
    }
}
