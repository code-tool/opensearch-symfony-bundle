<?php

namespace CodeTool\OpenSearch\Document;

interface ProviderInterface
{
    /**
     * @return iterable<string, array> documents keyed by their id
     */
    public function getDocuments(): iterable;

    /**
     * @return array|null the document, or null when nothing is to be indexed under the id
     */
    public function getDocument(string $id): ?array;
}
