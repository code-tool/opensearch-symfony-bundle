<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Index;

class IndexConfig
{
    public const string FIELD_TYPE = 'type';
    public const string FIELD_PATTERN = 'pattern';
    public const string FIELD_SETTINGS = 'settings';
    public const string FIELD_MAPPINGS = 'mappings';
    public const string TYPE_STATIC = 'static';
    public const string TYPE_TEMPLATE = 'template';

    private string $alias;

    private string $type;

    private ?DocumentGeneratorInterface $generator;

    private ?string $pattern;

    private array $settings;

    private array $mappings;

    public function __construct(
        string $alias,
        string $type,
        ?DocumentGeneratorInterface $generator = null,
        ?string $pattern = null,
        array $settings = [],
        array $mappings = []
    ) {
        $this->alias = $alias;
        $this->type = $type;
        $this->generator = $generator;
        $this->pattern = $pattern;
        $this->settings = $settings;
        $this->mappings = $mappings;
    }

    public function getName(): string
    {
        switch ($this->type) {
            case self::TYPE_STATIC:
                return $this->alias;
            case self::TYPE_TEMPLATE:
                $date = new \DateTimeImmutable();

                return \str_replace(
                    ['%Y', '%m', '%d'],
                    [$date->format('Y'), $date->format('m'), $date->format('d')],
                    $this->pattern
                );
            default:
                throw new \InvalidArgumentException(sprintf('Unknown type: %s', $this->type));
        }
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function getGenerator(): ?DocumentGeneratorInterface
    {
        return $this->generator;
    }

    public function toArray(): array
    {
        return [self::FIELD_SETTINGS => $this->settings, self::FIELD_MAPPINGS => $this->mappings];
    }
}