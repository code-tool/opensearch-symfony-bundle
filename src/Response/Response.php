<?php

namespace CodeTool\OpenSearch\Response;

class Response
{
    public const string FIELD_ACKNOWLEDGED = 'acknowledged';

    public function __construct(private readonly array $response) {}

    public function isAcknowledged(): bool
    {
        return \array_key_exists(self::FIELD_ACKNOWLEDGED, $this->response)
               && \is_bool($this->response[self::FIELD_ACKNOWLEDGED])
               && $this->response[self::FIELD_ACKNOWLEDGED];
    }

    public function getDocuments(): array
    {
        return $this->response;
    }
}