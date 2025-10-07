<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Field\FieldInterface;
use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

class DataStream
{
    public const string FIELD_BODY = 'body';
    public const string FIELD_NAME = 'name';
    public const string FIELD_DATA_STREAM = 'data_stream';
    public const string FIELD_TEMPLATE = 'template';
    public const string FIELD_DYNAMIC = 'dynamic';
    public const string FIELD_SETTINGS = 'settings';
    public const string FIELD_MAPPINGS = 'mappings';
    public const string FIELD_PROPERTIES = 'properties';
    public const string FIELD_INDEX_PATTERNS = 'index_patterns';
    public const string FIELD_TIMESTAMP_FIELD = 'timestamp_field';

    public function __construct(
        private readonly Client $client,
        private readonly FieldFactoryInterface $factory,
        private readonly string $name,
        private readonly array $patterns,
        private readonly string $timestampField,
        private readonly array $settings,
        private readonly mixed $dynamic,
        private readonly array $properties,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string,FieldInterface>
     */
    public function getProperties(): array
    {
        $properties = [];
        foreach ($this->properties as $name => $property) {
            $properties[$name] = $this->factory->create($name, $property);
        }

        return $properties;
    }

    public function create(): bool
    {
        return new Response(
                   $this->client->indices()->putIndexTemplate(
                       [
                           self::FIELD_NAME => $this->name,
                           self::FIELD_BODY => [
                               self::FIELD_INDEX_PATTERNS => $this->patterns,
                               self::FIELD_DATA_STREAM    => [self::FIELD_TIMESTAMP_FIELD => [self::FIELD_NAME => $this->timestampField]],
                               self::FIELD_TEMPLATE       => [
                                   self::FIELD_SETTINGS => $this->settings,
                                   self::FIELD_MAPPINGS => [
                                       self::FIELD_DYNAMIC    => $this->dynamic,
                                       self::FIELD_PROPERTIES => \array_map(
                                           static fn ($property) => $property->getDefinition(),
                                           $this->getProperties()
                                       )
                                   ]
                               ]
                           ]

                       ]
                   )
               )->isAcknowledged()
               && new Response(
                   $this->client->indices()->createDataStream(
                       [
                           self::FIELD_NAME => $this->name,
                           self::FIELD_BODY => []

                       ]
                   )
               )->isAcknowledged();
    }

    public function delete(): bool
    {
        return new Response(
                   $this->client->indices()->deleteDataStream([self::FIELD_NAME => $this->name])
               )->isAcknowledged()
               && new Response(
                   $this->client->indices()->deleteIndexTemplate([self::FIELD_NAME => $this->name])
               )->isAcknowledged();
    }

    public function exists(): bool
    {
        return $this->client->indices()->existsIndexTemplate([self::FIELD_NAME => $this->name]);
    }
}