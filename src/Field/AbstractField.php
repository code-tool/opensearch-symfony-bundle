<?php

namespace CodeTool\OpenSearch\Field;

abstract class AbstractField implements FieldInterface
{
    public const string FIELD_TYPE = 'type';
    public const string FIELD_INDEX = 'index';

    public function __construct(
        private readonly string $name,
        private readonly bool $index
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isIndex(): bool
    {
        return $this->index;
    }

    public function getDefinition(): array
    {
        return [
            self::FIELD_INDEX   => $this->index,
            self::FIELD_TYPE    => $this->getType()
        ];
    }

    abstract public function getType(): string;
}