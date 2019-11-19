<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class ReducerPass implements CompilerPassInterface
{
    use PluginTrait;

    private $reducerService;
    private $reducerTag;

    public function __construct(
        string $reducerService = 'mbt.reducer_manager',
        string $reducerTag = 'mbt.reducer'
    ) {
        $this->reducerService = $reducerService;
        $this->reducerTag = $reducerTag;
    }

    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->reducerService)) {
            return;
        }

        if (!$reducers = $this->findPlugins($container, $this->reducerTag)) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->reducerTag, $this->reducerService));
        }

        $reducerDefinition = $container->getDefinition($this->reducerService);
        $reducerDefinition->replaceArgument(0, $reducers);
    }
}
