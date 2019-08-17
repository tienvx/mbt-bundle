<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Command\CaptureScreenshotsCommand;
use Tienvx\Bundle\MbtBundle\Command\ExecuteTaskCommand;
use Tienvx\Bundle\MbtBundle\Command\TestModelCommand;
use Tienvx\Bundle\MbtBundle\Command\ReduceBugCommand;
use Tienvx\Bundle\MbtBundle\Command\ReduceStepsCommand;
use Tienvx\Bundle\MbtBundle\Command\TestSubjectCommand;

class SecurityTokenPass implements CompilerPassInterface
{
    private $tokenStorageService;

    public function __construct(string $tokenStorageService = 'security.token_storage')
    {
        $this->tokenStorageService = $tokenStorageService;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->tokenStorageService)) {
            return;
        }
        $tokenStorage = new Reference($this->tokenStorageService);

        $commands = [
            ExecuteTaskCommand::class,
            ReduceStepsCommand::class,
            TestModelCommand::class,
            ReduceBugCommand::class,
            CaptureScreenshotsCommand::class,
            TestSubjectCommand::class,
        ];
        foreach ($commands as $command) {
            $commandDefinition = $container->getDefinition($command);
            $commandDefinition->addMethodCall('setTokenStorage', [$tokenStorage]);
        }
    }
}
