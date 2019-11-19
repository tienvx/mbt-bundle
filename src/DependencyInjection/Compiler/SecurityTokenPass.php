<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection\Compiler;

use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tienvx\Bundle\MbtBundle\Helper\TokenHelper;

class SecurityTokenPass implements CompilerPassInterface
{
    private $tokenStorageService;

    public function __construct(string $tokenStorageService = 'security.token_storage')
    {
        $this->tokenStorageService = $tokenStorageService;
    }

    /**
     * @throws Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->tokenStorageService)) {
            return;
        }
        $tokenStorage = new Reference($this->tokenStorageService);

        $helperDefinition = $container->getDefinition(TokenHelper::class);
        $helperDefinition->addMethodCall('setTokenStorage', [$tokenStorage]);
    }
}
