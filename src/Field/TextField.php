<?php

namespace CodeTool\OpenSearch\Field;

class TextField extends AbstractField
{
    public const string FIELD_TYPE_TEXT = 'text';

    public function getType(): string
    {
        return self::FIELD_TYPE_TEXT;
    }
}