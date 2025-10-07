<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Field\FieldInterface;
use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

class Index extends AbstractStorage
{
    public const string FIELD_NAME = 'name';
    public const string FIELD_DYNAMIC = 'dynamic';
    public const string FIELD_SETTINGS = 'settings';
    public const string FIELD_MAPPINGS = 'mappings';
    public const string FIELD_PROPERTIES = 'properties';

    public function __construct(
        string $prefix,
        string $name,
        Client $client,
        private readonly FieldFactoryInterface $factory,
        private readonly array $settings,
        private readonly mixed $dynamic,
        private readonly array $properties,
    ) {
        parent::__construct($prefix, $name, $client);
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
            $this->getClient()->indices()->create(
                [
                    self::FIELD_INDEX => $this->getName(),
                    self::FIELD_BODY  => [
                        self::FIELD_SETTINGS => $this->settings,
                        self::FIELD_MAPPINGS => [
                            self::FIELD_DYNAMIC    => $this->dynamic,
                            self::FIELD_PROPERTIES => \array_map(
                                static fn ($property) => $property->getDefinition(),
                                $this->getProperties()
                            )
                        ]
                    ],
                ]
            )
        );
    }

    public function delete(): Response
    {
        return new Response($this->getClient()->indices()->delete([self::FIELD_INDEX => $this->getName()]));
    }

    public function exists(): bool
    {
        return $this->getClient()->indices()->exists([self::FIELD_INDEX => $this->getName()]);
    }
}