<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Command\ReportBugCommand;

class MonologChannelPass implements CompilerPassInterface
{
    private $loggerTag;

    public function __construct(string $loggerTag = 'monolog.logger.mbt')
    {
        $this->loggerTag = $loggerTag;
    }

    /**
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition($this->loggerTag)) {
            $logger = new Reference($this->loggerTag);

            $reportBugCommandDefinition = $container->getDefinition(ReportBugCommand::class);
            $reportBugCommandDefinition->addMethodCall('setLogger', [$logger]);
        }
    }
}
