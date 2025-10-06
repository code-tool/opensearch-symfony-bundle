<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Index;

use OpenSearch\Client;

class IndexManager
{
    public const string INDEX_FIELD_ACKNOWLEDGED = 'acknowledged';
    public const string INDEX_FIELD_INDEX = 'index';
    public const string INDEX_FIELD_BODY = 'body';
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

    public function exists(string $name): bool
    {
        return $this->client->indices()->exists([self::INDEX_FIELD_INDEX => $name]);
    }

    public function create(string $name): bool
    {
        $config = $this->getConfig($name);
        $request = [
            self::INDEX_FIELD_INDEX => $config->getName(),
            self::INDEX_FIELD_BODY  => $config->toArray(),
        ];

        return $this->responseToBool($this->client->indices()->create($request));
    }

    public function getConfig(string $name): IndexConfig
    {
        if (false === \array_key_exists($name, $this->indexes)) {
            throw new \InvalidArgumentException(\sprintf('Index "%s" not found', $name));
        }

        return $this->indexes[$name];
    }

    public function addDocument(string $name, array $document): bool
    {
        return true;
    }

    public function addDocuments(string $name, array $documents): bool
    {
        $bulk = [];
        $name = $this->getConfig($name)->getName();
        foreach ($documents as $document) {
            $bulk[] = [
                self::INDEX_FIELD_INDEX => [
                    self::INDEX_FIELD_ACTION_INDEX => $this->getConfig($name)->getName()
                ]
            ];
            $bulk[] = $document;

        }
        $this->client->bulk([self::INDEX_FIELD_INDEX => $name, self::INDEX_FIELD_BODY => $bulk]);

        return true;
    }

    /**
     * @return list<IndexConfig>
     */
    public function getIndexes(): iterable
    {
        yield from $this->indexes;
    }
}