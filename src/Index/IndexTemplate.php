<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Field\FieldInterface;
use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

class IndexTemplate
{
    public const string FIELD_BODY = 'body';
    public const string FIELD_NAME = 'name';
    public const string FIELD_TEMPLATE = 'template';
    public const string FIELD_DYNAMIC = 'dynamic';
    public const string FIELD_SETTINGS = 'settings';
    public const string FIELD_MAPPINGS = 'mappings';
    public const string FIELD_PROPERTIES = 'properties';
    public const string FIELD_INDEX_PATTERNS = 'index_patterns';

    public function __construct(
        private readonly string $name,
        private readonly Client $client,
        private readonly FieldFactoryInterface $factory,
        private readonly array $patterns,
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

    public function create(): Response
    {
        return new Response(
            $this->client->indices()->putIndexTemplate(
                [
                    self::FIELD_NAME => $this->name,
                    self::FIELD_BODY => [
                        self::FIELD_INDEX_PATTERNS => $this->patterns,
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
        );
    }

    public function delete(): bool
    {
        return new Response(
            $this->client->indices()->deleteIndexTemplate([self::FIELD_NAME => $this->name])
        )->isAcknowledged();
    }

    public function exists(): bool
    {
        return $this->client->indices()->existsIndexTemplate([self::FIELD_NAME => $this->name]);
    }
}