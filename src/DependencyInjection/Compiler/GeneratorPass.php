<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class GeneratorPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    private $generatorService;
    private $generatorTag;

    public function __construct(string $generatorService = 'mbt.generator_manager', string $generatorTag = 'mbt.generator')
    {
        $this->generatorService = $generatorService;
        $this->generatorTag = $generatorTag;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->generatorService)) {
            return;
        }

        if (!$generators = $this->findTaggedServices($this->generatorTag, $container)) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->generatorTag, $this->generatorService));
        }

        $generatorDefinition = $container->getDefinition($this->generatorService);
        $generatorDefinition->replaceArgument(0, $generators);
    }
}
