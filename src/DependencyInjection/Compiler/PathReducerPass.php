<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class PathReducerPass implements CompilerPassInterface
{
    use TaggedServiceTrait;

    private $pathReducerService;
    private $pathReducerTag;

    public function __construct(
        string $pathReducerService = 'mbt.path_reducer_manager',
        string $pathReducerTag = 'mbt.path_reducer'
    ) {
        $this->pathReducerService = $pathReducerService;
        $this->pathReducerTag     = $pathReducerTag;
    }

    /**
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->pathReducerService)) {
            return;
        }

        if (!$pathReducers = $this->findTaggedServices($container, $this->pathReducerTag, PluginInterface::class, 'getName')) {
            throw new RuntimeException(sprintf('You must tag at least one service as "%s" to use the "%s" service.', $this->pathReducerTag, $this->pathReducerService));
        }

        $pathReducerDefinition = $container->getDefinition($this->pathReducerService);
        $pathReducerDefinition->replaceArgument(0, $pathReducers);
    }
}
