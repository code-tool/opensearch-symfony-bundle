<?php

namespace CodeTool\OpenSearch\Field;

class IntegerField extends AbstractField
{
    public const string FIELD_TYPE_INTEGER = 'integer';

    public function getType(): string
    {
        return self::FIELD_TYPE_INTEGER;
    }
}