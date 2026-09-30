<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Index\AbstractStorage;
use CodeTool\OpenSearch\Index\DataStream;
use CodeTool\OpenSearch\Index\Index;
use CodeTool\OpenSearch\Index\IndexTemplate;
use OpenSearch\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class OpenSearchExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources')
        );
        $loader->load('services.yaml');

        foreach ($config['data_streams'] as $name => $data) {
            $this->processDataStreams($name, $config['prefix'], $data, $container);
        }
        foreach ($config['index_templates'] as $name => $data) {
            $this->processIndexTemplates($name, $config['prefix'], $data, $container);
        }
        foreach ($config['indexes'] as $name => $data) {
            $this->processIndexes($name, $config['prefix'], $data, $container);
        }
    }

    private function processDataStreams(string $name, string $prefix, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'opensearch.data_stream.' . $name,
            new Definition(DataStream::class)
                ->setArguments(
                    [
                        $prefix,
                        $name,
                        new Reference(Client::class),
                        new Reference(FieldFactoryInterface::class),
                        $config[DataStream::FIELD_INDEX_PATTERNS],
                        $config[DataStream::FIELD_TIMESTAMP_FIELD],
                        $config[DataStream::FIELD_SETTINGS] ?? [],
                        $config[DataStream::FIELD_MAPPINGS][DataStream::FIELD_DYNAMIC] ?? false,
                        $config[DataStream::FIELD_MAPPINGS][DataStream::FIELD_PROPERTIES] ?? [],
                    ]
                )
                ->addTag('opensearch.data_stream', ['name' => $name])
        );
    }

    private function processIndexTemplates(
        string $name,
        string $prefix,
        array $config,
        ContainerBuilder $container
    ): void {
        $container->setDefinition(
            'opensearch.index_template.' . $name,
            new Definition(IndexTemplate::class)
                ->setArguments(
                    [
                        $prefix,
                        $name,
                        new Reference(Client::class),
                        new Reference(FieldFactoryInterface::class),
                        $config[IndexTemplate::FIELD_INDEX_PATTERNS],
                        $config[IndexTemplate::FIELD_SETTINGS] ?? [],
                        $config[IndexTemplate::FIELD_MAPPINGS][IndexTemplate::FIELD_DYNAMIC] ?? false,
                        $config[IndexTemplate::FIELD_MAPPINGS][IndexTemplate::FIELD_PROPERTIES] ?? [],
                    ]
                )
                ->addTag('opensearch.index_template', ['name' => $name])
        );
    }

    private function processIndexes(string $name, string $prefix, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'opensearch.index.' . $name,
            new Definition(Index::class)
                ->setArguments(
                    [
                        $prefix,
                        $name,
                        new Reference(Client::class),
                        new Reference(FieldFactoryInterface::class),
                        $config[Index::FIELD_SETTINGS] ?? [],
                        $config[Index::FIELD_MAPPINGS][Index::FIELD_DYNAMIC] ?? false,
                        $config[Index::FIELD_MAPPINGS][Index::FIELD_PROPERTIES] ?? [],
                    ]
                )
                ->addTag('opensearch.index', ['name' => $name])
        );
    }

    public function getAlias(): string
    {
        return 'opensearch';
    }
}