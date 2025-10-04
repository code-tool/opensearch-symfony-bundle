<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Index;

class IndexConfigFactory
{
    private array $indexes;

    public function __construct(array $indexes = [])
    {
        $this->indexes = $indexes;
    }

    /**
     * @return iterable<string,IndexConfig>
     */
    public function getAll(): iterable
    {
        foreach ($this->indexes as $name => $config) {
            yield $name => $this->create($name);
        }
    }

    public function create(string $name): IndexConfig
    {
        if (false === \array_key_exists($name, $this->indexes)) {
            throw new \InvalidArgumentException(\sprintf('Index "%s" not found', $name));
        }
        $config = $this->indexes[$name];

        return new IndexConfig(
            $name,
            $config[IndexConfig::FIELD_TYPE],
            $config[IndexConfig::FIELD_PATTERN] ?? null,
            $config[IndexConfig::FIELD_SETTINGS] ?? [],
            $config[IndexConfig::FIELD_MAPPINGS] ?? []
        );
    }
}