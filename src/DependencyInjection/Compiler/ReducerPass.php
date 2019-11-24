<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Plugin\PluginFinder;

class ReducerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $reducerService;

    /**
     * @var string
     */
    private $reducerTag;

    public function __construct(
        string $reducerService = 'mbt.reducer_manager',
        string $reducerTag = 'mbt.reducer'
    ) {
        $this->reducerService = $reducerService;
        $this->reducerTag = $reducerTag;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->reducerService)) {
            return;
        }

        $finder = new PluginFinder($container);
        $reducers = $finder->find($this->reducerTag);

        if (!$reducers) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->reducerTag, $this->reducerService));
        }

        $reducerDefinition = $container->getDefinition($this->reducerService);
        $reducerDefinition->replaceArgument(0, $reducers);
    }
}
