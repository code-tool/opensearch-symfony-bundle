<?php

namespace CodeTool\OpenSearch\Field;

class FieldFactoryRegistry implements FieldFactoryInterface
{
    private array $factories = [];

    public function addFactory(string $type, FieldFactoryInterface $factory): static
    {
        $this->factories[$type] = $factory;

        return $this;
    }

    public function create(string $name, array $config): FieldInterface
    {
        if (false === \array_key_exists('type', $config)) {
            throw new \InvalidArgumentException('Field type not found');
        }

        return $this->getFactory((string)$config['type'])->create($name, $config);
    }

    public function getFactory(string $type): FieldFactoryInterface
    {
        if (false === \array_key_exists($type, $this->factories)) {
            throw new \InvalidArgumentException(\sprintf('Field factory for type "%s" not found', $type));
        }

        return $this->factories[$type];
    }
}