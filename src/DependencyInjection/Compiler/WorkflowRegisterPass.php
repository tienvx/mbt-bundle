<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Command\CaptureScreenshotsCommand;
use Tienvx\Bundle\MbtBundle\Command\CreateBugCommand;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Command\ReduceBugCommand;
use Tienvx\Bundle\MbtBundle\Command\ReduceStepsCommand;
use Tienvx\Bundle\MbtBundle\Command\TestBugCommand;
use Tienvx\Bundle\MbtBundle\Command\TestModelCommand;
use Tienvx\Bundle\MbtBundle\Command\TestPredefinedCaseCommand;
use Tienvx\Bundle\MbtBundle\Maker\MakeSubject;
use Tienvx\Bundle\MbtBundle\Reducer\LoopReducer;
use Tienvx\Bundle\MbtBundle\Reducer\RandomReducer;
use Tienvx\Bundle\MbtBundle\Reducer\SplitReducer;
use Tienvx\Bundle\MbtBundle\Reducer\TransitionReducer;
use Tienvx\Bundle\MbtBundle\Validator\Constraints\ModelValidator;

class WorkflowRegisterPass implements CompilerPassInterface
{
    private $registryService;

    public function __construct(string $registryService = 'workflow.registry')
    {
        $this->registryService = $registryService;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->registryService)) {
            return;
        }
        $registry = new Reference($this->registryService);

        $services = [
            CaptureScreenshotsCommand::class,
            CreateBugCommand::class,
            ExecuteTaskCommand::class,
            ReduceBugCommand::class,
            ReduceStepsCommand::class,
            TestBugCommand::class,
            TestModelCommand::class,
            TestPredefinedCaseCommand::class,
            MakeSubject::class,
            LoopReducer::class,
            RandomReducer::class,
            SplitReducer::class,
            TransitionReducer::class,
            ModelValidator::class,
        ];
        foreach ($services as $service) {
            $serviceDefinition = $container->getDefinition($service);
            $serviceDefinition->addMethodCall('setWorkflowRegistry', [$registry]);
        }
    }
}
