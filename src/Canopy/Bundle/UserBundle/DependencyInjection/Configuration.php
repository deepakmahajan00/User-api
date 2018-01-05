<?php

namespace Canopy\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('canopy_user');

        $rootNode
            ->children()
                ->arrayNode('media_web_paths')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->scalarNode('media_api_endpoint')
                    ->info('Endpoint pointing to the media application for media management.')
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
