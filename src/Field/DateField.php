<?php

namespace CodeTool\OpenSearch\Field;

class DateField extends AbstractField
{
    public const string FIELD_TYPE_DATE = 'date';
    public const string FIELD_FORMAT = 'format';

    public function __construct(
        string $name,
        bool $enabled,
        bool $index,
        private readonly string $format
    ) {
        parent::__construct($name, $enabled, $index);
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getDefinition(): array
    {
        return \array_merge(
            parent::getDefinition(),
            [
                self::FIELD_FORMAT => $this->format,
            ]
        );
    }

    public function getType(): string
    {
        return self::FIELD_TYPE_DATE;
    }
}