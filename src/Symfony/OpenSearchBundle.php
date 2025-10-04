<?php

declare(strict_types=1);

namespace CodeTool\OpenSearch\Symfony;

use CodeTool\OpenSearch\Symfony\DependencyInjection\OpenSearchExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSearchBundle extends Bundle
{
    public function getContainerExtension(): OpenSearchExtension
    {
        return new OpenSearchExtension();
    }
}