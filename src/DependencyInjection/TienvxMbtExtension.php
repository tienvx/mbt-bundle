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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Workflow;
use Tienvx\Bundle\MbtBundle\EventListener\ExpressionListener;
use Tienvx\Bundle\MbtBundle\Model;
use Tienvx\Bundle\MbtBundle\Service\DataProvider;
use Tienvx\Bundle\MbtBundle\Service\GeneratorDiscovery;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

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

        $bundleLoader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $bundleLoader->load('services.yml');

        /*foreach ($config['paths'] as $path) {
            $path = $container->getParameterBag()->resolveValue($path);
            $loader = new YamlFileLoader($container, new FileLocator($path));
            foreach (Finder::create()->followLinks()->files()->in($path)->name('/\.(ya?ml)$/') as $file) {
                $loader->load($file->getRealPath());
            }
        }*/

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

        $registryDefinition = $container->getDefinition(ModelRegistry::class);

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
            $definitionDefinition->addTag('workflow.definition', [
                'name' => $name,
                'type' => $type,
                'marking_store' => null,
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
            $registryDefinition->addMethodCall('add', [$name, new Reference($modelId)]);

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
                    new Reference(ExpressionLanguage::class),
                ]);

                $container->setDefinition(sprintf('%s.listener.expression', $modelId), $listener);
            }
            if ($dataConfiguration) {
                $dataProviderDefinition = $container->getDefinition(DataProvider::class);
                $dataProviderDefinition->setArguments([
                    $dataConfiguration,
                    new Reference(ExpressionLanguage::class),
                ]);
            }
        }
    }
}
