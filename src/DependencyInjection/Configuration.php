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
                ->scalarNode('selenium_dsn')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:4444/wd/hub')
                ->end()
                ->scalarNode('bug_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost/bug/%d')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
