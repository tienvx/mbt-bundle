<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tienvx_mbt');

        $rootNode
            ->children()
                ->integerNode('max_path_length')->defaultValue(300)->end()
                ->scalarNode('default_reporter')->defaultValue('null')->end()
                ->arrayNode('reporter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('email')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('from')->defaultValue([])->end()
                                ->variableNode('to')->defaultValue([])->end()
                            ->end()
                        ->end() // email
                    ->end()
                ->end() // reporter
            ->end()
        ;

        return $treeBuilder;
    }
}
