<?php

declare(strict_types=1);

namespace App\Application\Transformer;

/**
 * Interface for response transformers.
 *
 * Each transformer is responsible for transforming a specific part of the
 * aggregated data into the final JSON response.
 *
 * To add a new transformer:
 * 1. Create a class implementing this interface
 * 2. Tag it with 'app.response_transformer' in services.yaml
 * 3. The system will automatically register and execute it
 *
 * @example
 * ```yaml
 * App\Application\Transformer\Impl\SignaturesTransformer:
 *     tags:
 *         - { name: 'app.response_transformer', priority: 90 }
 * ```
 */
interface ResponseTransformerInterface
{
    /**
     * Get the unique key for this transformer.
     * Used as identifier in pipeline.
     *
     * @return string The unique identifier (e.g., 'editorial', 'body', 'signatures')
     */
    public function getKey(): string;

    /**
     * Check if this transformer should run for the given context.
     * Allows conditional execution based on editorial type, available data, etc.
     *
     * @param TransformationContext $context The transformation context
     *
     * @return bool True if this transformer should execute
     */
    public function supports(TransformationContext $context): bool;

    /**
     * Transform data and add to response.
     * Modifies context.response directly.
     *
     * @param TransformationContext $context The transformation context
     */
    public function transform(TransformationContext $context): void;

    /**
     * Get the priority of this transformer.
     * Higher priority transformers run first.
     *
     * @return int The priority (default: 0)
     */
    public function getPriority(): int;
}
