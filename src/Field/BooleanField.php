<?php

namespace CodeTool\OpenSearch\Field;

class BooleanField extends AbstractField
{
    public const string FIELD_TYPE_BOOLEAN = 'boolean';

    public function getType(): string
    {
        return self::FIELD_TYPE_BOOLEAN;
    }
}