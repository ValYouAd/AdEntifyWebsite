<?php

namespace AdEntify\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ad_entify_core');

        $rootNode->children()
            ->arrayNode('foursquare')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('client_secret')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()
            ->arrayNode('amazon_s3')
                ->children()
                    ->scalarNode('base_url')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
