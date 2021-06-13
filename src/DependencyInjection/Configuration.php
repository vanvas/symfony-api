<?php
declare(strict_types=1);

namespace Vim\Api\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('api');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('cors')
                    ->isRequired()
                    ->children()
                        ->scalarNode('allow_origin')->isRequired()->end()
                        ->scalarNode('allow_headers')->defaultValue('Authorization, Content-Type')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
