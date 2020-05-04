<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\EventListener\GuardListener;
use Tienvx\Bundle\MbtBundle\Model\SubjectInterface;

class GuardPass implements CompilerPassInterface
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
        foreach ($container->findTaggedServiceIds($this->definitionTag, true) as $serviceId => $attributes) {
            $name = $attributes[0]['name'];
            $type = $attributes[0]['type'];
            $workflowId = sprintf('%s.%s', $type, $name);
            $guardId = sprintf('%s.listener.guard', $workflowId);

            if (!$this->isModel($container, $workflowId) || !$container->hasDefinition($guardId)) {
                continue;
            }

            $this->replaceDefinition($container, $guardId);
        }
    }

    protected function isModel(ContainerBuilder $container, string $workflowId): bool
    {
        $registryDefinition = $container->getDefinition('workflow.registry');
        $calls = $registryDefinition->getMethodCalls();

        foreach ($calls as $i => $call) {
            if ('addWorkflow' === $call[0] && $workflowId === (string) $call[1][0] && $call[1][1] instanceof Definition) {
                return in_array(SubjectInterface::class, $call[1][1]->getArguments());
            }
        }

        return false;
    }

    protected function replaceDefinition(ContainerBuilder $container, string $guardId)
    {
        $guardsConfiguration = $container->getDefinition($guardId)->getArgument(0);
        $tags = $container->getDefinition($guardId)->getTags();
        $guard = $container->setDefinition(
            $guardId,
            new Definition(GuardListener::class, [
                $guardsConfiguration,
                new Definition(ExpressionLanguage::class),
            ])
        );
        $guard->setTags($tags);
    }
}
