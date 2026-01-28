<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Application\Pipeline\AggregatorPipeline;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged aggregators with the AggregatorPipeline.
 *
 * To add a new aggregator:
 * 1. Create a class implementing AsyncAggregatorInterface
 * 2. Tag it with 'app.aggregator' in services.yaml
 * 3. This pass will automatically register it
 */
class AggregatorPipelineCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(AggregatorPipeline::class)) {
            return;
        }

        $pipelineDefinition = $container->findDefinition(AggregatorPipeline::class);
        $aggregators = $container->findTaggedServiceIds('app.aggregator');

        foreach ($aggregators as $serviceId => $tags) {
            $pipelineDefinition->addMethodCall('addAggregator', [
                new Reference($serviceId),
            ]);
        }
    }
}
