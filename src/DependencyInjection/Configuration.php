<?php

namespace Tienvx\Bundle\MbtBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mbt');

        $this->addModelSection($rootNode);
        //$this->addGeneratorSection($rootNode);

        return $treeBuilder;
    }

    private function addModelSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('model')
            ->children()
                ->arrayNode('models')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('tag')
                        ->fixXmlConfig('place')
                        ->fixXmlConfig('transition')
                        ->children()
                            ->arrayNode('tags')
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->scalarNode('subject')
                                ->cannotBeEmpty()
                                ->info('A class that implement the model')
                                ->example('MyBundle\\Entity\\ShoppingCart')
                            ->end()
                            ->scalarNode('label')
                                ->defaultValue('')
                                ->info('Which part of system that this model describe')
                                ->example('Shopping Cart')
                            ->end()
                            ->scalarNode('initial_place')
                                ->defaultNull()
                            ->end()
                            ->arrayNode('places')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(function ($s) {
                                            return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $s) !== 1;
                                        })
                                        ->thenInvalid('Invalid place name')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('transitions')
                                ->beforeNormalization()
                                    ->always()
                                    ->then(function ($transitions) {
                                        // It's an indexed array, we let the validation occurs
                                        if (isset($transitions[0])) {
                                            return $transitions;
                                        }

                                        foreach ($transitions as $name => $transition) {
                                            if (array_key_exists('name', $transition)) {
                                                continue;
                                            }
                                            $transition['name'] = $name;
                                            $transitions[$name] = $transition;
                                        }

                                        return $transitions;
                                    })
                                ->end()
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($s) {
                                                    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $s) !== 1;
                                                })
                                                ->thenInvalid('Invalid transition name')
                                            ->end()
                                        ->end()
                                        ->scalarNode('guard')
                                            ->cannotBeEmpty()
                                            ->info('An expression to block the transition')
                                            ->example('is_fully_authenticated() and has_role(\'ROLE_JOURNALIST\') and subject.getTitle() == \'My first article\'')
                                        ->end()
                                        ->scalarNode('from')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($s) {
                                                    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $s) !== 1;
                                                })
                                                ->thenInvalid('Invalid place name')
                                            ->end()
                                        ->end()
                                        ->scalarNode('to')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($s) {
                                                    return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $s) !== 1;
                                                })
                                                ->thenInvalid('Invalid place name')
                                            ->end()
                                        ->end()
                                        ->integerNode('weight')
                                            // 1 for one step to go from a place to other place.
                                            ->defaultValue(1)
                                            ->min(1)
                                        ->end()
                                        ->scalarNode('label')
                                            ->defaultNull()
                                        ->end()
                                        ->arrayNode('data')
                                            ->defaultValue([])
                                            ->prototype('scalar')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return !$v['subject'];
                            })
                            ->thenInvalid('"subject" should be configured.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addGeneratorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('path')
            ->children()
                ->arrayNode('paths')
                    ->prototype('scalar')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
