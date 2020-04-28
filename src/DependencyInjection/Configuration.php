<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                ->integerNode('max_steps')->defaultValue(300)->end()
                ->floatNode('transition_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->floatNode('place_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->scalarNode('default_bug_title')->defaultValue('New bug')->end()
                ->scalarNode('admin_url')->defaultValue('')->end()
                ->scalarNode('email_from')->defaultValue('')->end()
            ->end()
        ;

        $this->addPredefinedCaseSection($rootNode);

        return $treeBuilder;
    }

    private function addPredefinedCaseSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->fixXmlConfig('predefined_case')
            ->children()
                ->arrayNode('predefined_cases')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')
                                ->defaultValue('')
                            ->end()
                            ->scalarNode('workflow')
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('steps')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('transition')->end()
                                        ->arrayNode('data')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('key')->end()
                                                    ->scalarNode('value')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
