<?php

namespace CodeTool\OpenSearch\Field;

abstract class AbstractField implements FieldInterface
{
    public const string FIELD_TYPE = 'type';

    public function __construct(private readonly string $name) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefinition(): array
    {
        return [self::FIELD_TYPE => $this->getType()];
    }

    abstract public function getType(): string;
}