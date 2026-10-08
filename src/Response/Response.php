<?php

namespace CodeTool\OpenSearch\Response;

class Response
{
    public const string FIELD_ACKNOWLEDGED = 'acknowledged';
    public const string FIELD_HITS = 'hits';
    public const string FIELD_SOURCE = '_source';
    public const string FIELD_ID = '_id';

    public function __construct(private readonly array $response) {}

    public function isAcknowledged(): bool
    {
        return \array_key_exists(self::FIELD_ACKNOWLEDGED, $this->response)
               && \is_bool($this->response[self::FIELD_ACKNOWLEDGED])
               && $this->response[self::FIELD_ACKNOWLEDGED];
    }

    /**
     * @return iterable<string, array> the sources of the hits, keyed by their id
     */
    public function getDocuments(): iterable
    {
        if (false === \array_key_exists(self::FIELD_HITS, $this->response)) {
            return [];
        }
        if (false === \array_key_exists(self::FIELD_HITS, $this->response[self::FIELD_HITS])) {
            return [];
        }
        foreach ($this->response[self::FIELD_HITS][self::FIELD_HITS] ?? [] as $document) {
            yield $document[self::FIELD_ID] => $document[self::FIELD_SOURCE] ?? [];
        }
    }
}