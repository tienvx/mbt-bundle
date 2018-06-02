<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class StopConditionPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    private $stopConditionService;
    private $stopConditionTag;

    public function __construct(string $stopConditionService = 'mbt.stop_condition_manager', string $stopConditionTag = 'mbt.stop_condition')
    {
        $this->stopConditionService = $stopConditionService;
        $this->stopConditionTag     = $stopConditionTag;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->stopConditionService)) {
            return;
        }

        if (!$stopConditions = $this->findTaggedServices($this->stopConditionTag, $container)) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->stopConditionTag, $this->stopConditionService));
        }

        $stopConditionDefinition = $container->getDefinition($this->stopConditionService);
        $stopConditionDefinition->replaceArgument(0, $stopConditions);
    }
}
