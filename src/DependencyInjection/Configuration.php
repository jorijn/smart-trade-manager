<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuiler = new TreeBuilder('app');

        $treeBuiler->getRootNode()
            ->children()
                ->arrayNode('exchange')
                    ->children()
                        ->scalarNode('api_key')->isRequired()->end()
                        ->scalarNode('api_secret')->isRequired()->end()
                        ->integerNode('ladder_size')->defaultValue(10)->end()
                    ->end()
            ->end()
        ;

        return $treeBuiler;
    }
}
