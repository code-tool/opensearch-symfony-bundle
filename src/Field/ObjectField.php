<?php

namespace CodeTool\OpenSearch\Field;

class ObjectField extends AbstractField
{
    public const string FIELD_TYPE_OBJECT = 'object';
    public const string FIELD_DYNAMIC = 'dynamic';
    public const string FIELD_ENABLED = 'enabled';
    public const string FIELD_PROPERTIES = 'properties';

    public function __construct(
        string $name,
        private readonly FieldFactoryInterface $factory,
        private readonly bool $enabled,
        private readonly bool $dynamic,
        private readonly array $properties
    ) {
        parent::__construct($name);
    }

    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getDefinition(): array
    {
        $properties = [];
        foreach ($this->properties as $name => $property) {
            $properties[$name] = $this->factory->create($name, $property)->getDefinition();
        }

        return \array_merge(
            parent::getDefinition(),
            [
                self::FIELD_ENABLED    => $this->enabled,
                self::FIELD_DYNAMIC    => $this->dynamic,
                self::FIELD_PROPERTIES => $properties
            ]
        );
    }

    public function getType(): string
    {
        return self::FIELD_TYPE_OBJECT;
    }
}