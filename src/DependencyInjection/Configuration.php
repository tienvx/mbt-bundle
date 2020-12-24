<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const SELENIUM_SERVER = 'selenium_server';
    public const MAX_STEPS = 'max_steps';
    public const PROVIDERS = 'providers';
    public const PLATFORMS = 'platforms';
    public const RESOLUTIONS = 'resolutions';
    public const BROWSERS = 'browsers';
    public const VERSIONS = 'versions';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tienvx_mbt');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode(static::MAX_STEPS)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue(150)
                ->end()
                //->fixXmlConfig('provider')
                ->arrayNode(static::PROVIDERS)
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->fixXmlConfig('platform')
                        ->children()
                            ->scalarNode(static::SELENIUM_SERVER)
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->defaultValue('http://localhost:4444')
                            ->end()
                            ->arrayNode(static::PLATFORMS)
                                ->normalizeKeys(false)
                                ->useAttributeAsKey('name')
                                    ->arrayPrototype()
                                    ->fixXmlConfig('browser')
                                    ->fixXmlConfig('resolution')
                                    ->children()
                                        ->arrayNode(static::BROWSERS)
                                            ->normalizeKeys(false)
                                            ->useAttributeAsKey('name')
                                                ->arrayPrototype()
                                                ->fixXmlConfig('version')
                                                ->children()
                                                    ->arrayNode(static::VERSIONS)
                                                        ->beforeNormalization()->castToArray()->end()
                                                        ->defaultValue([])
                                                        ->prototype('scalar')->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode(static::RESOLUTIONS)
                                            ->beforeNormalization()->castToArray()->end()
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
