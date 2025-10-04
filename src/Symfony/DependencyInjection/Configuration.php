<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getPropertiesNode() : ArrayNodeDefinition
    {
        $root = new NodeBuilder();

        return $root
            ->arrayNode('properties')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->enumNode('type')
                            ->values(
                                [
                'keyword',
                'text',
                'match_only_text',
                'token_count',
                'wildcard',
                'binary',
                'boolean',
                'byte',
                'double',
                'float',
                'half_float',
                'integer',
                'long',
                'short',
                'unsigned_long',
                'scaled_float',
                'date',
                'date_nanos',
                'ip',
                'knn_vector',
                'integer_range',
                'long_range',
                'double_range',
                'float_range',
                'ip_range',
                'date_range',
                'object',
                'nested',
                'flat_object',
                'join',
                'completion',
                'search_as_you_type',
                'geo_point',
                'geo_shape',
                'xy_point',
                'xy_shape',
                'rank_feature',
                'rank_features'
            ]
                            )
                            ->isRequired()
                        ->end()
                        ->scalarNode('format')->end()
                        ->scalarNode('analyzer')->end()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->booleanNode('index')->defaultTrue()->end()
                        ->booleanNode('dynamic')->defaultTrue()->end()
                    ->end()
                ->end()
                ->validate()
                    ->ifTrue(function ($v) {
                        return $v['type'] === 'object' && empty($v['properties']);
                    })
                    ->thenInvalid('Properties is required when type is "object"')
                ->end();
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('opensearch');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->enumNode('type')
                                ->values(['template', 'static'])
                                ->isRequired()
                            ->end()
                            ->scalarNode('pattern')
                                ->info('Date pattern for template indexes, e.g., logs_%Y-%m-%d')
                            ->end()
                            ->arrayNode('settings')
                                ->ignoreExtraKeys(false)
                                ->variablePrototype()->end()
                            ->end()
                            ->arrayNode('mappings')
                                ->children()
                                    ->booleanNode('dynamic')->defaultTrue()->end()
                                    ->append($this->getPropertiesNode())->end()
                                ->end()
                            ->end()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->ignoreExtraKeys(false)
                                    ->children()
                                        ->scalarNode('type')->end()
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('analyzers')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('type')->end()
                                        ->scalarNode('tokenizer')->end()
                                        ->arrayNode('filter')
                                            ->scalarPrototype()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->validate()
                            ->ifTrue(function ($v) {
                                return $v['type'] === 'template' && empty($v['pattern']);
                            })
                            ->thenInvalid('Pattern is required when type is "template"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}