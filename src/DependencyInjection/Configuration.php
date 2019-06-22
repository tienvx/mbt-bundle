<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tienvx_mbt');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('max_path_length')->defaultValue(300)->end()
                ->floatNode('transition_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->floatNode('place_coverage')->defaultValue(100)->min(0)->max(100)->end()
                ->scalarNode('default_bug_title')->defaultValue('New bug found')->end()
                // 'https://hooks.slack.com/...'
                ->scalarNode('slack_hook_url')->defaultValue('')->end()
                // 'QA Team'
                ->scalarNode('slack_from')->defaultValue('')->end()
                // '#a-channel' or '@a-person'
                ->scalarNode('slack_to')->defaultValue('')->end()
                ->scalarNode('slack_message')->defaultValue('')->end()
                ->scalarNode('email_from')->defaultValue('')->end()
                ->scalarNode('email_to')->defaultValue('')->end()
                ->scalarNode('email_subject')->defaultValue('')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
