<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Helper\WorkflowHelper;

class WorkflowRegisterPass implements CompilerPassInterface
{
    private $registryService;

    public function __construct(string $registryService = 'workflow.registry')
    {
        $this->registryService = $registryService;
    }

    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->registryService)) {
            return;
        }
        $registry = new Reference($this->registryService);

        $helperDefinition = $container->getDefinition(WorkflowHelper::class);
        $helperDefinition->addMethodCall('setWorkflowRegistry', [$registry]);
    }
}
