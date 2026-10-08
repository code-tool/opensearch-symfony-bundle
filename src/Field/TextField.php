<?php

namespace CodeTool\OpenSearch\Field;

class TextField extends AbstractField
{
    public const string FIELD_TYPE_TEXT = 'text';
    public const string FIELD_INDEX = 'index';
    public const string FIELD_ANALYZER = 'analyzer';
    public const string FIELD_SEARCH_ANALYZER = 'search_analyzer';

    public function __construct(
        string $name,
        private readonly bool $index,
        private readonly ?string $analyzer = null,
        private readonly ?string $searchAnalyzer = null,
    ) {
        parent::__construct($name);
    }

    public function isIndex(): bool
    {
        return $this->index;
    }

    public function getDefinition(): array
    {
        return \array_merge(
            parent::getDefinition(),
            [self::FIELD_INDEX => $this->index],
            \array_filter(
                [self::FIELD_ANALYZER => $this->analyzer, self::FIELD_SEARCH_ANALYZER => $this->searchAnalyzer],
                static fn (?string $analyzer) => null !== $analyzer
            )
        );
    }

    public function getType(): string
    {
        return self::FIELD_TYPE_TEXT;
    }
}