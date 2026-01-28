<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Application\Pipeline\TransformerPipeline;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged transformers with the TransformerPipeline.
 *
 * To add a new transformer:
 * 1. Create a class implementing ResponseTransformerInterface
 * 2. Tag it with 'app.response_transformer' in services.yaml
 * 3. This pass will automatically register it
 */
class TransformerPipelineCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(TransformerPipeline::class)) {
            return;
        }

        $pipelineDefinition = $container->findDefinition(TransformerPipeline::class);
        $transformers = $container->findTaggedServiceIds('app.response_transformer');

        foreach ($transformers as $serviceId => $tags) {
            $pipelineDefinition->addMethodCall('addTransformer', [
                new Reference($serviceId),
            ]);
        }
    }
}
