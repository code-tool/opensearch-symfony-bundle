<?php

namespace CodeTool\OpenSearch\Field;

class FloatField extends AbstractField
{
    public const string FIELD_TYPE_FLOAT = 'float';

    public function getType(): string
    {
        return self::FIELD_TYPE_FLOAT;
    }
}