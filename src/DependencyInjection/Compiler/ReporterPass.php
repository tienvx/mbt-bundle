<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tienvx\Bundle\MbtBundle\Plugin\PluginFinder;

class ReporterPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $reporterService;

    /**
     * @var string
     */
    private $reporterTag;

    public function __construct(
        string $reporterService = 'mbt.reporter_manager',
        string $reporterTag = 'mbt.reporter'
    ) {
        $this->reporterService = $reporterService;
        $this->reporterTag = $reporterTag;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->reporterService)) {
            return;
        }

        $finder = new PluginFinder($container);
        $reporters = $finder->find($this->reporterTag);

        $reporterDefinition = $container->getDefinition($this->reporterService);
        $reporterDefinition->replaceArgument(0, $reporters);
    }
}
