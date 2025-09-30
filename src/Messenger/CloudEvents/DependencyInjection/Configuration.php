<?php

namespace Stegeman\Messenger\CloudEvents\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cloud_events');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()
            ->scalarNode('normalizer_service_id')
                ->defaultValue('Stegeman\Messenger\CloudEvents\Normalizer\V1\Normalizer')
            ->end()
            ->arrayNode('registry')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('className')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(fn(string $v) => !class_exists($v))
                                ->thenInvalid('Class %s does not exist.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}