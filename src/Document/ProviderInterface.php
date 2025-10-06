<?php

namespace CodeTool\OpenSearch\Document;

interface ProviderInterface
{
    /**
     * @return iterable<array>
     */
    public function getDocuments(): iterable;
}