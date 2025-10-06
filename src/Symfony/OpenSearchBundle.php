<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony;

use CodeTool\OpenSearch\Symfony\DependencyInjection\IndexConfigCompilerPass;
use CodeTool\OpenSearch\Symfony\DependencyInjection\OpenSearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new IndexConfigCompilerPass());
    }

    public function getContainerExtension(): OpenSearchExtension
    {
        return new OpenSearchExtension();
    }
}