<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Helper\GuardHelper;
use Tienvx\Bundle\MbtBundle\Helper\ModelHelper;

class ModelPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $definitionTag;

    public function __construct(string $definitionTag = 'workflow.definition')
    {
        $this->definitionTag = $definitionTag;
    }

    public function process(ContainerBuilder $container): void
    {
        $modelHelperDefinition = $container->getDefinition(ModelHelper::class);
        $guardHelperDefinition = $container->getDefinition(GuardHelper::class);
        foreach ($container->findTaggedServiceIds($this->definitionTag, true) as $serviceId => $attributes) {
            $definition = $container->getDefinition($serviceId);
            if ($this->isModel($definition)) {
                $name = $attributes[0]['name'];
                $type = $attributes[0]['type'];
                $modelHelperDefinition->addMethodCall('addModel', [$name, $type, new Reference($serviceId)]);

                $this->mergeGuardConfiguration($container, $type, $name, $guardHelperDefinition);
            }
        }
        $guardHelperDefinition->addMethodCall('setExpressionLanguage', [new Reference('workflow.security.expression_language')]);
    }

    protected function isModel(Definition $definition): bool
    {
        $metadataStoreDefinition = $definition->getArgument(3);
        if (!$metadataStoreDefinition instanceof Definition) {
            return false;
        }

        $modelMetadata = $metadataStoreDefinition->getArgument(0);
        if (!is_array($modelMetadata) || !isset($modelMetadata['model']) || true !== $modelMetadata['model']) {
            return false;
        }

        return true;
    }

    protected function mergeGuardConfiguration(ContainerBuilder $container, string $type, string $name, Definition $guardHelperDefinition): void
    {
        $guardId = sprintf('%s.%s.listener.guard', $type, $name);
        if ($container->hasDefinition($guardId)) {
            $guardDefinition = $container->getDefinition($guardId);
            $guardHelperDefinition->addMethodCall('mergeConfiguration', [$guardDefinition->getArgument(0)]);
        }
    }
}
