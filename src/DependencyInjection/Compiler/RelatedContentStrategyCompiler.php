<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Application\Strategy\RelatedContent\RelatedContentHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for registering related content strategies.
 * Follows Open/Closed Principle - new strategies added without modifying existing code.
 */
class RelatedContentStrategyCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(RelatedContentHandler::class)) {
            return;
        }

        $handlerDefinition = $container->findDefinition(RelatedContentHandler::class);
        $strategies = $container->findTaggedServiceIds('app.related_content_strategy');

        foreach ($strategies as $serviceId => $tags) {
            $handlerDefinition->addMethodCall('addStrategy', [new Reference($serviceId)]);
        }
    }
}
