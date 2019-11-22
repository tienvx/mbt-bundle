<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

class WorkflowRegisterPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $registryService;

    public function __construct(string $registryService = 'workflow.registry')
    {
        $this->registryService = $registryService;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has($this->registryService)) {
            return;
        }
        $registry = new Reference($this->registryService);

        $helperDefinition = $container->getDefinition(WorkflowHelper::class);
        $helperDefinition->addMethodCall('setWorkflowRegistry', [$registry]);
    }
}
