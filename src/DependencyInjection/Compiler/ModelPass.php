<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Workflow\Definition;
use Tienvx\Bundle\MbtBundle\Model\Subject;
use Tienvx\Bundle\MbtBundle\Service\ModelRegistry;

class ModelPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ModelRegistry::class)) {
            return;
        }

        $definition = $container->findDefinition(ModelRegistry::class);

        $taggedServices = $container->findTaggedServiceIds('workflow.definition');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['name']) || !isset($tag['type']) || $tag['type'] !== 'state_machine') {
                    continue;
                }
                $workflowDefinition = $container->get($id);
                if (!$workflowDefinition instanceof Definition) {
                    continue;
                }
                $workflowMetadata = $workflowDefinition->getMetadataStore()->getWorkflowMetadata();
                if (!isset($workflowMetadata['subject']) || !is_subclass_of($workflowMetadata['subject'], Subject::class)) {
                    continue;
                }
                // to become a model, a workflow must meet these 3 conditions: type = state_machine, metadata has subject,
                // and subject is a sub-class of Tienvx\Bundle\MbtBundle\Model\Subject
                $definition->addMethodCall('addModel', array($tag['name'], $workflowMetadata));
            }
        }
    }
}
