<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tienvx_mbt');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('selenium_server')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:4444')
                ->end()
                ->scalarNode('provider_name')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('selenoid')
                ->end()
                ->scalarNode('admin_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
