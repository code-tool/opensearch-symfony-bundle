<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Index\DataStream;
use CodeTool\OpenSearch\Index\Index;
use CodeTool\OpenSearch\Index\IndexTemplate;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const array FIELD_TYPES
        = [
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
        ];

    public const array FILTER_TYPES
        = [
            'apostrophe',
            'asciifolding',
            'cjk_bigram',
            'cjk_width',
            'classic',
            'common_grams',
            'conditional',
            'decimal_digit',
            'delimited_payload',
            'delimited_term_freq',
            'dictionary_decompounder',
            'edge_ngram',
            'elision',
            'fingerprint',
            'flatten_graph',
            'hunspell',
            'hyphenation_decompounder',
            'keep_types',
            'keep_words',
            'keyword_marker',
            'keyword_repeat',
            'kstem',
            'kuromoji_completion',
            'length',
            'limit',
            'lowercase',
            'min_hash',
            'multiplexer',
            'ngram',
            'arabic_normalization',
            'german_normalization',
            'hindi_normalization',
            'indic_normalization',
            'sorani_normalization',
            'persian_normalization',
            'scandinavian_normalization',
            'scandinavian_folding',
            'serbian_normalization',
            'pattern_capture',
            'pattern_replace',
            'phonetic',
            'porter_stem',
            'predicate_token_filter',
            'remove_duplicates',
            'reverse',
            'shingle',
            'snowball',
            'stemmer',
            'stemmer_override',
            'stop',
            'synonym',
            'synonym_graph',
            'trim',
            'truncate',
            'unique',
            'uppercase',
            'word_delimiter',
            'word_delimiter_graph'

        ];

    public const array ANALYZER_TYPES = [
        'standard',
        'simple',
        'whitespace',
        'stop',
        'keyword',
        'pattern',
        'arabic',
        'armenian',
        'basque',
        'bengali',
        'brazilian',
        'bulgarian',
        'catalan',
        'czech',
        'danish',
        'dutch',
        'english',
        'estonian',
        'finnish',
        'french',
        'galician',
        'german',
        'greek',
        'hindi',
        'hungarian',
        'indonesian',
        'irish',
        'italian',
        'latvian',
        'lithuanian',
        'norwegian',
        'persian',
        'portuguese',
        'romanian',
        'russian',
        'sorani',
        'spanish',
        'swedish',
        'thai',
        'turkish',
        'fingerprint',
        'bert-uncased',
        'mbert-uncased',
        'custom'
    ];

    private function getPropertiesNode(int $depth = 0) : ArrayNodeDefinition
    {
        $builder =  new NodeBuilder()
            ->arrayNode(Index::FIELD_PROPERTIES)
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->enumNode('type')
                            ->values(self::FIELD_TYPES)
                            ->isRequired()
                        ->end()
                        ->scalarNode('format')->end()
                        ->scalarNode('analyzer')->end()
                        ->scalarNode('search_analyzer')->end()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->booleanNode('index')->defaultTrue()->end()
                        ->booleanNode('dynamic')->defaultTrue()->end();
        if ($depth < 8) {
            $builder->append($this->getPropertiesNode($depth + 1));
        }

        return $builder
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return $v['type'] === 'object' && $v['dynamic'] === false && empty($v['properties']);
                        })
                        ->thenInvalid('Properties is required when type is "object"')
                    ->end()
                ->end();
    }

    private function getSettingsNode() : ArrayNodeDefinition
    {
        return new NodeBuilder()
            ->arrayNode(Index::FIELD_SETTINGS)
                ->ignoreExtraKeys(false)
                ->children()
                    ->arrayNode('analysis')
                        ->children()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->ignoreExtraKeys(false)
                                    ->children()
                                        ->enumNode('type')
                                            ->values(self::FILTER_TYPES)
                                            ->isRequired()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('analyzers')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->enumNode('type')
                                            ->values(self::ANALYZER_TYPES)
                                            ->isRequired()
                                        ->end()
                                        ->arrayNode('char_filter')
                                            ->scalarPrototype()->end()
                                        ->end()
                                        ->scalarNode('tokenizer')->end()
                                        ->arrayNode('filter')
                                            ->scalarPrototype()->end()
                                        ->end()
                                        ->scalarNode('position_increment_gap')->end()
                                    ->end()
                                    ->validate()
                                        ->ifTrue(function ($v) {
                                            return $v['type'] === 'custom' && empty($v['tokenizer']);
                                        })
                                        ->thenInvalid('Tokenizer is required when type is "custom"')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }

    public function getIndexesNode(): ArrayNodeDefinition
    {
        return new NodeBuilder()
            ->arrayNode('indexes')
                ->useAttributeAsKey(Index::FIELD_NAME)
                ->arrayPrototype()
                    ->children()
                        ->append($this->getSettingsNode())
                        ->arrayNode(Index::FIELD_MAPPINGS)
                            ->children()
                                ->booleanNode(Index::FIELD_DYNAMIC)->defaultTrue()->end()
                                ->append($this->getPropertiesNode())
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }

    public function getDataStreamsNode(): ArrayNodeDefinition
    {
        return new NodeBuilder()
            ->arrayNode('data_streams')
                ->useAttributeAsKey(DataStream::FIELD_NAME)
                ->arrayPrototype()
                    ->children()
                        ->arrayNode(DataStream::FIELD_INDEX_PATTERNS)
                            ->info('Date pattern for template indexes, e.g., logs_%Y-%m-%d')
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode(DataStream::FIELD_TIMESTAMP_FIELD)
                            ->defaultValue('@timestamp')
                            ->info('Name of the template field for the data stream index')
                        ->end()
                        ->append($this->getSettingsNode())
                        ->arrayNode(DataStream::FIELD_MAPPINGS)
                            ->children()
                                ->booleanNode(DataStream::FIELD_DYNAMIC)->defaultTrue()->end()
                                ->append($this->getPropertiesNode())
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }

    public function getIndexTemplatesNode(): ArrayNodeDefinition
    {
        return new NodeBuilder()
            ->arrayNode('index_templates')
                ->useAttributeAsKey(IndexTemplate::FIELD_NAME)
                ->arrayPrototype()
                    ->children()
                        ->arrayNode(IndexTemplate::FIELD_INDEX_PATTERNS)
                            ->info('Date pattern for template indexes, e.g., logs_%Y-%m-%d')
                            ->scalarPrototype()->end()
                        ->end()
                        ->append($this->getSettingsNode())
                        ->arrayNode(IndexTemplate::FIELD_MAPPINGS)
                            ->children()
                                ->booleanNode(IndexTemplate::FIELD_DYNAMIC)->defaultTrue()->end()
                                ->append($this->getPropertiesNode())
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('opensearch');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->append($this->getDataStreamsNode())
                ->append($this->getIndexTemplatesNode())
                ->append($this->getIndexesNode())
            ->end();

        return $treeBuilder;
    }
}