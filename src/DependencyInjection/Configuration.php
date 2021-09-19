<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const WEBDRIVER_URI = 'webdriver_uri';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tienvx_mbt');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode(static::WEBDRIVER_URI)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('http://localhost:4444')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
