<?php

namespace CodeTool\OpenSearch\Index;

use CodeTool\OpenSearch\Response\Response;
use OpenSearch\Client;

class AbstractStorage
{
    public const string FIELD_PREFIX = 'prefix';
    public const string FIELD_BODY = 'body';
    public const string FIELD_CREATE = 'create';
    public const string FIELD_INDEX = 'index';
    public const string FIELD_UNDERSCORE_INDEX = '_index';
    public const string FIELD_DOC = 'doc';
    public const string FIELD_DOC_AS_UPSERT = 'doc_as_upsert';
    public const string FIELD_QUERY = 'query';

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

    public function upsert(array $document): Response
    {
        return new Response(
            $this->client->update(
                [
                    self::FIELD_INDEX => $this->name,
                    self::FIELD_BODY  => [self::FIELD_DOC => $document, self::FIELD_DOC_AS_UPSERT => true]
                ]
            )
        );
    }

    public function update(array $document): Response
    {
        return new Response($this->client->index([self::FIELD_INDEX => $this->name, self::FIELD_BODY => $document]));
    }

    public function find(array $query): Response
    {
        return new Response(
            $this->client->delete(
                [
                    self::FIELD_INDEX => $this->name,
                    self::FIELD_BODY  => [
                        self::FIELD_QUERY => $query
                    ]
                ]
            )
        );

    }
}