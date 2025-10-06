<?php

namespace CodeTool\OpenSearch\Field;

class BuiltinFieldFactory implements FieldFactoryInterface
{
    public function create(string $name, array $config): FieldInterface
    {
        if (false === \array_key_exists('type', $config)) {
            throw new \InvalidArgumentException('Field type not found');
        }

        return match ((string)$config['type']) {
            BooleanField::FIELD_TYPE_BOOLEAN => new BooleanField(
                $name,
                $config[BooleanField::FIELD_INDEX] ?? true,
            ),
            DateField::FIELD_TYPE_DATE       => new DateField(
                $name,
                $config[DateField::FIELD_INDEX] ?? true,
                $config[DateField::FIELD_FORMAT] ?? null
            ),
            FloatField::FIELD_TYPE_FLOAT     => new FloatField(
                $name,
                $config[FloatField::FIELD_INDEX] ?? true,
            ),
            IntegerField::FIELD_TYPE_INTEGER => new IntegerField(
                $name,
                $config[IntegerField::FIELD_INDEX] ?? true,
            ),
            KeywordField::FIELD_TYPE_KEYWORD => new KeywordField(
                $name,
                $config[KeywordField::FIELD_INDEX] ?? true,
            ),
            ObjectField::FIELD_TYPE_OBJECT   => new ObjectField(

                $name,
                $this,
                $config[ObjectField::FIELD_ENABLED] ?? true,
                $config[ObjectField::FIELD_DYNAMIC] ?? true,
                $config[ObjectField::FIELD_PROPERTIES] ?? []
            ),
            TextField::FIELD_TYPE_TEXT       => new TextField(
                $name,
                $config[TextField::FIELD_INDEX] ?? true,
            ),
            default                          => throw new \InvalidArgumentException(
                \sprintf('Unknown field type: %s', $config['type'])
            ),
        };
    }

}