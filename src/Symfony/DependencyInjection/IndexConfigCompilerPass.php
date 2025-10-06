<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Index\IndexManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IndexConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(IndexManager::class)) {
            return;
        }
        $manager = $container->getDefinition(IndexManager::class);
        foreach ($container->findTaggedServiceIds('opensearch.index.config') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addIndex', [$tag['name'], $container->getDefinition($id)]);
            }
        }
    }
}