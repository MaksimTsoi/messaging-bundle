<?php

namespace Tsoi\EventBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const CURRENT_MS = 'current_microservice';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tsoi_event_bus');

        $rootNode
            ->children()
                ->arrayNode('microservices')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('connection')
                                ->children()
                                    ->scalarNode('host')->end()
                                    ->integerNode('port')->end()
                                    ->scalarNode('user_name')->end()
                                    ->scalarNode('password')->end()
                                    ->scalarNode('vhost')->end()
                                    ->arrayNode('ssl_options')->prototype('scalar')->end()->end()
                                    ->arrayNode('options')->prototype('scalar')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('exchange')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultValue('tsoi-exchange.topic')->end()
                                    ->scalarNode('type')->defaultValue('topic')->end()
                                    ->booleanNode('passive')->defaultFalse()->end()
                                    ->booleanNode('auto_delete')->defaultFalse()->end()
                                    ->booleanNode('internal')->defaultFalse()->end()
                                    ->booleanNode('nowait')->defaultFalse()->end()
                                    ->arrayNode('arguments')->prototype('scalar')->end()->end()
                                    ->scalarNode('ticket')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->arrayNode('queue')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('force_declare')->defaultFalse()->end()
                                    ->booleanNode('passive')->defaultFalse()->end()
                                    ->booleanNode('exclusive')->defaultFalse()->end()
                                    ->booleanNode('auto_delete')->defaultFalse()->end()
                                    ->booleanNode('nowait')->defaultFalse()->end()
                                    ->arrayNode('data')->prototype('scalar')->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('consumer')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('tag')->defaultValue('')->end()
                                    ->booleanNode('no_local')->defaultFalse()->end()
                                    ->booleanNode('no_ack')->defaultFalse()->end()
                                    ->booleanNode('exclusive')->defaultFalse()->end()
                                    ->booleanNode('nowait')->defaultFalse()->end()
                                    ->booleanNode('persistent')->defaultTrue()->end()
                                ->end()
                            ->end()
                            ->arrayNode('integration_events')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('event')->end()
                                        ->scalarNode('event_handler')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}