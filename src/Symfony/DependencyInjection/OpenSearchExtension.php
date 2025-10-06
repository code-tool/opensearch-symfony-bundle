<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Index\DataStream;
use CodeTool\OpenSearch\Index\Index;
use CodeTool\OpenSearch\Index\IndexTemplate;
use OpenSearch\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
            $this->processDataStreams($name, $data, $container);
        }
        foreach ($config['index_templates'] as $name => $data) {
            $this->processIndexTemplates($name, $data, $container);
        }
        foreach ($config['indexes'] as $name => $data) {
            $this->processIndexes($name, $data, $container);
        }
    }

    private function processDataStreams(string $name, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'opensearch.data_stream.' . $name,
            new Definition(DataStream::class)
                ->setArguments(
                    [
                        new Reference(Client::class),
                        new Alias(FieldFactoryInterface::class),
                        $name,
                        $config['pattern'],
                        $config['timestamp_field'],
                        $config['settings'] ?? [],
                        $config['mappings']['dynamic'] ?? false,
                        $config['mappings']['properties'] ?? [],
                    ]
                )
                ->addTag('opensearch.data_stream', ['name' => $name])
        );
    }

    private function processIndexTemplates(string $name, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'opensearch.index_template.' . $name,
            new Definition(IndexTemplate::class)
                ->setArguments(
                    [
                        new Reference(Client::class),
                        new Alias(FieldFactoryInterface::class),
                        $name,
                        $config['pattern'],
                        $config['settings'] ?? [],
                        $config['mappings']['dynamic'] ?? false,
                        $config['mappings']['properties'] ?? [],
                    ]
                )
                ->addTag('opensearch.index_template', ['name' => $name])
        );
    }

    private function processIndexes(string $name, array $config, ContainerBuilder $container): void
    {
        $container->setDefinition(
            'opensearch.index.' . $name,
            new Definition(Index::class)
                ->setArguments(
                    [
                        new Reference(Client::class),
                        new Alias(FieldFactoryInterface::class),
                        $name,
                        $config['settings'] ?? [],
                        $config['mappings']['dynamic'] ?? false,
                        $config['mappings']['properties'] ?? [],
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