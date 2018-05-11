<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Definition as WorkflowDefinition;
use Tienvx\Bundle\MbtBundle\EventListener\ModelGuardListener;
use Tienvx\Bundle\MbtBundle\Model\Subject;

class ModelGuardPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('workflow.definition');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['name']) || !isset($tag['type']) || $tag['type'] !== 'state_machine') {
                    continue;
                }
                $workflowDefinition = $container->get($id);
                if (!$workflowDefinition instanceof WorkflowDefinition) {
                    continue;
                }
                $workflowMetadata = $workflowDefinition->getMetadataStore()->getWorkflowMetadata();
                if (!isset($workflowMetadata['subject']) || !is_subclass_of($workflowMetadata['subject'], Subject::class)) {
                    continue;
                }
                if (empty($workflowMetadata['model'])) {
                    continue;
                }
                // to become a model, a workflow must meet these 3 conditions: type = state_machine, metadata has
                // subject that is a sub-class of Tienvx\Bundle\MbtBundle\Model\Subject, and metadata has model = true

                // Add Guard Listener
                $guard = new Definition(ModelGuardListener::class);
                $guard->setPrivate(true);
                $configuration = array();
                foreach ($workflowDefinition->getTransitions() as $transition) {
                    $transitionMetadata = $workflowDefinition->getMetadataStore()->getTransitionMetadata($transition);
                    if (!isset($transitionMetadata['model_guard'])) {
                        continue;
                    }

                    $eventName = sprintf('workflow.%s.guard.%s', $tag['name'], $transition->getName());
                    $guard->addTag('kernel.event_listener', array('event' => $eventName, 'method' => 'onTransition'));
                    $configuration[$eventName] = $transitionMetadata['model_guard'];
                }
                if ($configuration) {
                    $guard->setArguments(array(
                        $configuration,
                        new Reference(ExpressionLanguage::class),
                    ));

                    $workflowId = sprintf('%s.%s', $tag['type'], $tag['name']);
                    $container->setDefinition(sprintf('%s.listener.model_guard', $workflowId), $guard);
                }
            }
        }
    }
}
