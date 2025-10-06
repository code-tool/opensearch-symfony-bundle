<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Field\FieldFactoryInterface;
use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class IndexCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(Manager::class)) {
            return;
        }
        $manager = $container->getDefinition(Manager::class);
        foreach ($container->findTaggedServiceIds('opensearch.index') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addIndex', [$tag['name'], new Reference($id)]);
            }
        }
    }
}