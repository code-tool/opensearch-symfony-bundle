<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasAlias(Manager::class)) {
            return;
        }
        $manager = $container->getDefinition(Manager::class);
        foreach ($container->findTaggedServiceIds('opensearch.field.factory') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addFactory', [$tag['field'], new Reference($id)]);
            }
        }
    }
}