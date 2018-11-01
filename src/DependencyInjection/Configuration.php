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
                ->arrayNode('generator')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_path_length')->defaultValue(300)->end()
                        ->floatNode('transition_coverage')->defaultValue(100)->min(0)->max(100)->end()
                        ->floatNode('place_coverage')->defaultValue(100)->min(0)->max(100)->end()
                    ->end()
                ->end() // generator
                ->arrayNode('command')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_bug_title')->defaultValue('')->end()
                    ->end()
                ->end() // command
                ->arrayNode('subjects')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end() // subjects
            ->end()
        ;

        return $treeBuilder;
    }
}
