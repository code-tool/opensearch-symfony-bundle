<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

abstract class AbstractStorage
{
    public const string FIELD_PREFIX = 'prefix';
    public const string FIELD_BODY = 'body';
    public const string FIELD_CREATE = 'create';
    public const string FIELD_INDEX = 'index';
    public const string FIELD_ID = 'id';
    public const string FIELD_UNDERSCORE_INDEX = '_index';
    public const string FIELD_DOC = 'doc';
    public const string FIELD_DOC_AS_UPSERT = 'doc_as_upsert';
    public const string FIELD_QUERY = 'query';
    public const string FIELD_SORT = 'sort';
    public const string FIELD_ORDER = 'order';
    public const string FIELD_ORDER_ASC = 'asc';
    public const string FIELD_ORDER_DESC = 'desc';
    public const string FIELD_FROM = 'from';
    public const string FIELD_SIZE = 'size';
    public const string FIELD_QUERY_BOOL = 'bool';
    public const string FIELD_QUERY_MUST = 'must';
    public const string FIELD_QUERY_TERM = 'term';

    public function __construct(
        private readonly string $prefix,
        private readonly string $name,
        private readonly Client $client
    ) {}

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function add(array $document): Response
    {
        return new Response(
            $this->client->index([self::FIELD_INDEX => $this->getName(), self::FIELD_BODY => $document])
        );
    }

    public function getName(): string
    {
        return $this->prefix . $this->name;
    }

    public function addBulk(array $documents): Response
    {
        $body = [];
        foreach ($documents as $document) {
            $body[] = [self::FIELD_CREATE => [self::FIELD_UNDERSCORE_INDEX => $this->getName()]];
            $body[] = $document;
        }

        return new Response(
            $this->client->bulk([self::FIELD_INDEX => $this->getName(), self::FIELD_BODY => $body])
        );
    }

    public function upsert(string $id, array $document): Response
    {
        return new Response(
            $this->client->update(
                [
                    self::FIELD_INDEX => $this->getName(),
                    self::FIELD_ID    => $id,
                    self::FIELD_BODY  => [self::FIELD_DOC => $document, self::FIELD_DOC_AS_UPSERT => true]
                ]
            )
        );
    }

    public function remove(string $id): Response
    {
        return new Response($this->client->delete([self::FIELD_INDEX => $this->getName(), self::FIELD_ID => $id]));
    }

    public function update(array $document): Response
    {
        return new Response(
            $this->client->index([self::FIELD_INDEX => $this->getName(), self::FIELD_BODY => $document])
        );
    }

    public function find(array $query, array $sort = [], int $limit = 30, int $offset = 0): Response
    {
        return new Response(
            $this->client->search(
                [
                    self::FIELD_INDEX => [$this->getName()],
                    self::FIELD_BODY  => [
                        self::FIELD_FROM  => $offset,
                        self::FIELD_SIZE  => $limit,
                        self::FIELD_QUERY => $query,
                        self::FIELD_SORT  => $sort
                    ]
                ]
            )
        );
    }
}