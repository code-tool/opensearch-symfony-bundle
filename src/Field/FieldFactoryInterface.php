<?php

namespace CodeTool\OpenSearch\Field;

interface FieldFactoryInterface
{
    public function create(string $name, array $config): FieldInterface;
}