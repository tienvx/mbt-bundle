<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class ReporterPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    private $reporterService;
    private $reporterTag;

    public function __construct(string $reporterService = 'mbt.reporter_manager', string $reporterTag = 'mbt.reporter')
    {
        $this->reporterService = $reporterService;
        $this->reporterTag = $reporterTag;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->reporterService)) {
            return;
        }

        if (!$reporters = $this->findTaggedServices($container, $this->reporterTag)) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->reporterTag, $this->reporterService));
        }

        $reporterDefinition = $container->getDefinition($this->reporterService);
        $reporterDefinition->replaceArgument(0, $reporters);
    }
}
