<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class IndexTemplateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->has(Manager::class)) {
            return;
        }
        $manager = $container->getDefinition(Manager::class);
        foreach ($container->findTaggedServiceIds('opensearch.index_template') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addIndexTemplate', [$tag['name'], new Reference($id)]);
            }
        }
    }
}