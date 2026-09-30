<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony;

use CodeTool\OpenSearch\Symfony\DependencyInjection\DataStreamCompilerPass;
use CodeTool\OpenSearch\Symfony\DependencyInjection\FieldFactoryCompilerPass;
use CodeTool\OpenSearch\Symfony\DependencyInjection\IndexCompilerPass;
use CodeTool\OpenSearch\Symfony\DependencyInjection\IndexTemplateCompilerPass;
use CodeTool\OpenSearch\Symfony\DependencyInjection\OpenSearchExtension;
use CodeTool\OpenSearch\Symfony\DependencyInjection\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container
            ->addCompilerPass(new FieldFactoryCompilerPass())
            ->addCompilerPass(new ProviderCompilerPass())
            ->addCompilerPass(new IndexCompilerPass())
            ->addCompilerPass(new IndexTemplateCompilerPass())
            ->addCompilerPass(new DataStreamCompilerPass());
    }

    public function getContainerExtension(): OpenSearchExtension
    {
        return new OpenSearchExtension();
    }
}