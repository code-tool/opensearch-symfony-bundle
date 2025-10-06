<?php

namespace CodeTool\OpenSearch\Index;

interface DocumentGeneratorInterface
{
    /**
     * @return iterable<array>
     */
    public function getDocuments(): iterable;
}