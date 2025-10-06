<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

class Index
{
    public const string FIELD_INDEX = 'index';
    public const string FIELD_UNDERSCORE_INDEX = '_index';
    public const string FIELD_BODY = 'body';
    public const string FIELD_DYNAMIC = 'dynamic';
    public const string FIELD_SETTINGS = 'settings';
    public const string FIELD_MAPPINGS = 'mappings';
    public const string FIELD_PROPERTIES = 'properties';
    public const string FIELD_DOC = 'doc';
    public const string FIELD_DOC_AS_UPSERT = 'doc_as_upsert';

    public function __construct(
        private readonly Client $client,
        private readonly FieldFactoryInterface $factory,
        private readonly string $name,
        private readonly array $settings,
        private readonly mixed $dynamic,
        private readonly array $properties,
    ) {}

    public function create(): Response
    {
        $request = [
            self::FIELD_INDEX => $this->name,
            self::FIELD_BODY  => [
                self::FIELD_SETTINGS => $this->settings,
                self::FIELD_MAPPINGS => [
                    self::FIELD_DYNAMIC    => $this->dynamic,
                    self::FIELD_PROPERTIES => \array_map(
                        static fn ($name, $definition): array => $this->factory
                            ->create($name, $definition)
                            ->getDefinition(),
                        $this->properties,
                    )
                ]
            ],
        ];

        return new Response($this->client->indices()->create($request));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function delete(): Response
    {
        return new Response($this->client->indices()->delete([self::FIELD_INDEX => $this->name]));
    }

    public function exists(): bool
    {
        return $this->client->indices()->exists([self::FIELD_INDEX => $this->name]);
    }

    public function add(array $document): Response
    {
        return new Response($this->client->index([self::FIELD_INDEX => $this->name, self::FIELD_BODY => $document]));
    }

    public function addBulk(array $documents): Response
    {
        $body = [];
        foreach ($documents as $document) {
            $body[] = [self::FIELD_INDEX => [self::FIELD_UNDERSCORE_INDEX => $this->name]];
            $body[] = $document;
        }

        return new Response(
            $this->client->bulk([self::FIELD_INDEX => $this->name, self::FIELD_BODY => $body])
        );
    }

    public function upsert(array $document): Response
    {
        return new Response(
            $this->client->update(
                [
                    self::FIELD_INDEX => $this->name,
                    self::FIELD_BODY  => [self::FIELD_DOC => $document, self::FIELD_DOC_AS_UPSERT => true]
                ]
            )
        );
    }

    public function update(array $document): Response
    {
        return new Response($this->client->index([self::FIELD_INDEX => $this->name, self::FIELD_BODY => $document]));
    }
}