<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const SELENOID_SERVER = 'selenoid_server';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tienvx_mbt');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode(static::SELENOID_SERVER)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:4444')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
