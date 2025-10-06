<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Index;

use OpenSearch\Client;

class IndexManager
{
    public const string INDEX_FIELD_ACKNOWLEDGED = 'acknowledged';
    public const string INDEX_FIELD_INDEX = 'index';
    public const string INDEX_FIELD_BODY = 'body';
    public const string INDEX_FIELD_NAME = 'name';
    public const string INDEX_FIELD_ACTION_INDEX = '_index';
    /**
     * @var array<string,IndexConfig>
     */
    private array $indexes = [];

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addIndex(string $name, IndexConfig $config): IndexManager
    {
        $this->indexes[$name] = $config;

        return $this;
    }

    public function delete(string $name): bool
    {
        return $this->responseToBool($this->client->indices()->delete([self::INDEX_FIELD_INDEX => $name]));
    }

    protected function responseToBool(array $response): bool
    {
        return \array_key_exists(self::INDEX_FIELD_ACKNOWLEDGED, $response)
               && \is_bool($response[self::INDEX_FIELD_ACKNOWLEDGED])
               && $response[self::INDEX_FIELD_ACKNOWLEDGED];
    }

    public function addDocument(string $name, array $document): bool
    {
        $config = $this->getConfig($name);

        return $this->responseToBool(
            $this->client->index(
                [
                    self::INDEX_FIELD_INDEX => $config->getName(new \DateTimeImmutable()),
                    self::INDEX_FIELD_BODY  => $document
                ]
            )
        );

    }

    public function getConfig(string $name): IndexConfig
    {
        if (false === \array_key_exists($name, $this->indexes)) {
            throw new \InvalidArgumentException(\sprintf('Index "%s" not found', $name));
        }

        return $this->indexes[$name];
    }

    public function addDocuments(string $name, array $documents): bool
    {
        $index = $this->ensureIndex($name);
        $bulk = [];
        foreach ($documents as $document) {
            $bulk[] = [self::INDEX_FIELD_INDEX => [self::INDEX_FIELD_ACTION_INDEX => $index]];
            $bulk[] = $document;

        }

        return $this->responseToBool(
            $this->client->bulk([self::INDEX_FIELD_INDEX => $name, self::INDEX_FIELD_BODY => $bulk])
        );
    }

    public function ensureIndex(string $name): string
    {
        $config = $this->getConfig($name);
        $index = $config->getName(new \DateTimeImmutable());
        switch ($config->getType()) {
            case IndexConfig::TYPE_STATIC:
            case IndexConfig::TYPE_TEMPLATE:
                if ($this->client->indices()->exists([self::INDEX_FIELD_INDEX => $index])) {
                    return $index;
                }
                if (false === $this->create($name)) {
                    throw new \RuntimeException(\sprintf('Failed to create index "%s"', $name));
                }

                return $index;
            case IndexConfig::TYPE_DATA_STREAM:
                return $config->getAlias();
            default:
                throw new \InvalidArgumentException(\sprintf('Unknown type: %s', $config->getType()));
        }
    }

    public function exists(string $name): bool
    {
        return $this->client->indices()->exists([self::INDEX_FIELD_INDEX => $name]);
    }

    public function create(string $name): bool
    {
        $config = $this->getConfig($name);
        switch ($config->getType()) {
            case IndexConfig::TYPE_DATA_STREAM:
                return true;
            case IndexConfig::TYPE_STATIC:
                $request = [
                    self::INDEX_FIELD_INDEX => $config->getName(new \DateTimeImmutable()),
                    self::INDEX_FIELD_BODY  => $config->toArray(),
                ];

                return $this->responseToBool($this->client->indices()->create($request));
            case IndexConfig::TYPE_TEMPLATE:
                if (false === $this->client->indices()->existsIndexTemplate(
                        [
                            self::INDEX_FIELD_NAME => $config->getAlias()
                        ]
                    )) {
                    $this->client->indices()->putIndexTemplate(
                        [
                            self::INDEX_FIELD_NAME => $config->getAlias(),
                            self::INDEX_FIELD_BODY => $config->toArray()
                        ]
                    );
                }

                $request = [
                    self::INDEX_FIELD_INDEX => $config->getName(new \DateTimeImmutable()),
                    self::INDEX_FIELD_BODY  => $config->toArray(),
                ];

                return $this->responseToBool($this->client->indices()->create($request));

            default:
                throw new \InvalidArgumentException(\sprintf('Unknown type: %s', $config->getType()));
        }
    }

    /**
     * @return list<IndexConfig>
     */
    public function getIndexes(): iterable
    {
        yield from $this->indexes;
    }
}