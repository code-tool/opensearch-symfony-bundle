<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Document\ProviderInterface;

class Manager
{
    /**
     * @var array<string,Index>
     */
    private array $indexes = [];

    /**
     * @var array<string,ProviderInterface>
     */
    private array $providers = [];

    /**
     * @var array<string,DataStream>
     */
    private array $dataStreams = [];

    /**
     * @var array<string,IndexTemplate>
     */
    private array $indexTemplates = [];

    public function addIndex(string $name, Index $index): Manager
    {
        $this->indexes[$name] = $index;

        return $this;
    }

    public function addDataStream(string $name, DataStream $dataStream): Manager
    {
        $this->dataStreams[$name] = $dataStream;

        return $this;
    }

    public function addIndexTemplate(string $name, IndexTemplate $indexTemplate): Manager
    {
        $this->indexTemplates[$name] = $indexTemplate;

        return $this;
    }

    public function delete(string $name): bool
    {
        return $this->getIndex($name)->delete()->isAcknowledged();
    }

    public function getIndex(string $name): Index
    {
        if (false === \array_key_exists($name, $this->indexes)) {
            throw new \InvalidArgumentException(\sprintf('Index "%s" not found', $name));
        }

        return $this->indexes[$name];
    }

    public function getDataStream(string $name): DataStream
    {
        if (false === \array_key_exists($name, $this->dataStreams)) {
            throw new \InvalidArgumentException(\sprintf('Data stream "%s" not found', $name));
        }

        return $this->dataStreams[$name];
    }

    public function getIndexTemplate(string $name): IndexTemplate
    {
        if (false === \array_key_exists($name, $this->indexTemplates)) {
            throw new \InvalidArgumentException(\sprintf('Index template "%s" not found', $name));
        }

        return $this->indexTemplates[$name];
    }

    public function exists(string $name): bool
    {
        return $this->getIndex($name)->exists();
    }

    public function reindex(string $name): bool
    {
        foreach ($this->getProvider($name) as $document) {
            $this->getIndex($name)->upsert($document);
        }

        return true;
    }

    public function getProvider(string $name): ProviderInterface
    {
        if (false === \array_key_exists($name, $this->providers)) {
            throw new \InvalidArgumentException(\sprintf('Provider "%s" not found', $name));
        }

        return $this->providers[$name];
    }

    public function create(string $name): bool
    {
        return $this->getIndex($name)->create()->isAcknowledged();
    }

    /**
     * @return list<Index>
     */
    public function getIndexes(): iterable
    {
        yield from $this->indexes;
    }
}