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
                ->floatNode('transition_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->floatNode('place_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->scalarNode('default_bug_title')->defaultValue('')->end()
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
                        ->arrayNode('hipchat')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('address')->defaultValue('https://api.hipchat.com/v2')->end()
                                ->scalarNode('room')->defaultValue('')->end()
                                ->scalarNode('token')->defaultValue('')->end()
                                ->enumNode('color')->defaultValue('purple')->values(['yellow', 'green', 'red', 'purple', 'gray', 'random'])->end()
                                ->booleanNode('notify')->defaultValue(false)->end()
                                ->enumNode('format')->defaultValue('html')->values(['html', 'text'])->end()
                            ->end()
                        ->end() // hipchat
                        ->arrayNode('slack')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('address')->defaultValue('https://slack.com/api')->end()
                                ->scalarNode('channel')->defaultValue('')->end()
                                ->scalarNode('token')->defaultValue('')->end()
                            ->end()
                        ->end() // slack
                        ->arrayNode('github')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('address')->defaultValue('https://api.github.com')->end()
                                ->scalarNode('repoOwner')->defaultValue('')->end()
                                ->scalarNode('repoName')->defaultValue('')->end()
                                ->scalarNode('token')->defaultValue('')->end()
                            ->end()
                        ->end() // github
                    ->end()
                ->end() // reporter
                ->arrayNode('subjects')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end() // subjects
            ->end()
        ;

        return $treeBuilder;
    }
}
