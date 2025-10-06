<?php

namespace CodeTool\OpenSearch\Field;

class KeywordField extends AbstractField
{
    public const string FIELD_TYPE_KEYWORD = 'keyword';

    public function getType(): string
    {
        return self::FIELD_TYPE_KEYWORD;
    }
}