<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Plugin\PluginFinder;

class GeneratorPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $generatorService;

    /**
     * @var string
     */
    private $generatorTag;

    public function __construct(
        string $generatorService = 'mbt.generator_manager',
        string $generatorTag = 'mbt.generator'
    ) {
        $this->generatorService = $generatorService;
        $this->generatorTag = $generatorTag;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->generatorService)) {
            return;
        }

        $finder = new PluginFinder($container);
        $generators = $finder->find($this->generatorTag);

        if (!$generators) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->generatorTag, $this->generatorService));
        }

        $generatorDefinition = $container->getDefinition($this->generatorService);
        $generatorDefinition->replaceArgument(0, $generators);
    }
}
