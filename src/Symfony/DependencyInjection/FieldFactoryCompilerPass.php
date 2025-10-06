<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FieldFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(FieldFactoryInterface::class)) {
            return;
        }
        $manager = $container->getDefinition(FieldFactoryInterface::class);
        foreach ($container->findTaggedServiceIds('opensearch.field.factory') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addFactory', [$tag['name'], $container->getDefinition($id)]);
            }
        }
    }
}