<?php

namespace CodeTool\OpenSearch\Symfony\DependencyInjection;

use CodeTool\OpenSearch\Index\Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition(Manager::class)) {
            return;
        }
        $manager = $container->getDefinition(Manager::class);
        foreach ($container->findTaggedServiceIds('opensearch.document_provider') as $id => $tags) {
            foreach ($tags as $tag) {
                $manager->addMethodCall('addProvider', [$tag['name'], new Reference($id)]);
            }
        }
    }
}