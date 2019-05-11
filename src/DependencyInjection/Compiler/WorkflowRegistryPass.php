<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Command\CaptureScreenshotsCommand;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Command\TestModelCommand;
use Tienvx\Bundle\MbtBundle\Command\TestSubjectCommand;
use Tienvx\Bundle\MbtBundle\Maker\MakeSubject;
use Tienvx\Bundle\MbtBundle\Validator\Constraints\ModelValidator;

class WorkflowRegistryPass implements CompilerPassInterface
{
    private $pathReducerTag;
    private $workflowRegistry;

    public function __construct(
        string $pathReducerTag = 'mbt.path_reducer',
        string $workflowRegistry = 'workflow.registry'
    ) {
        $this->pathReducerTag = $pathReducerTag;
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition($this->workflowRegistry)) {
            $workflowRegistry = new Reference($this->workflowRegistry);

            foreach ($container->findTaggedServiceIds($this->pathReducerTag, true) as $serviceId => $attributes) {
                $pathReducer = $container->getDefinition($serviceId);
                $pathReducer->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);
            }

            $executeTaskCommandDefinition = $container->getDefinition(ExecuteTaskCommand::class);
            $executeTaskCommandDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);

            $testModelCommandDefinition = $container->getDefinition(TestModelCommand::class);
            $testModelCommandDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);

            $testSubjectCommandDefinition = $container->getDefinition(TestSubjectCommand::class);
            $testSubjectCommandDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);

            $captureScreenshotsCommandDefinition = $container->getDefinition(CaptureScreenshotsCommand::class);
            $captureScreenshotsCommandDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);

            $modelValidatorDefinition = $container->getDefinition(ModelValidator::class);
            $modelValidatorDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);

            if ($container->hasDefinition(MakeSubject::class)) {
                $makeSubjectDefinition = $container->getDefinition(MakeSubject::class);
                $makeSubjectDefinition->addMethodCall('setWorkflowRegistry', [$workflowRegistry]);
            }
        }
    }
}
