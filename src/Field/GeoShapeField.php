<?php

namespace CodeTool\OpenSearch\Field;

class GeoShapeField extends AbstractField
{
    public const string FIELD_TYPE_GEO_SHAPE = 'geo_shape';
    public const string FIELD_INDEX = 'index';

    public function __construct(string $name, private readonly bool $index)
    {
        parent::__construct($name);
    }

    public function isIndex(): bool
    {
        return $this->index;
    }

    public function getDefinition(): array
    {
        return \array_merge(parent::getDefinition(), [self::FIELD_INDEX => $this->index]);
    }

    public function getType(): string
    {
        return self::FIELD_TYPE_GEO_SHAPE;
    }
}