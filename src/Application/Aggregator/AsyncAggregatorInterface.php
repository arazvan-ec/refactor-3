<?php

declare(strict_types=1);

namespace App\Application\Aggregator;

/**
 * Interface for async data aggregators.
 *
 * Each aggregator is responsible for fetching a specific type of data
 * (tags, signatures, multimedia, etc.) and can be executed in parallel.
 *
 * To add a new aggregator:
 * 1. Create a class implementing this interface
 * 2. Tag it with 'app.aggregator' in services.yaml
 * 3. The system will automatically register and execute it
 *
 * @example
 * ```yaml
 * App\Application\Aggregator\Impl\TagsAggregator:
 *     tags:
 *         - { name: 'app.aggregator', priority: 10 }
 * ```
 */
interface AsyncAggregatorInterface
{
    /**
     * Get the unique key for this aggregator's data.
     * This key will be used in the aggregation context.
     *
     * @return string The unique identifier (e.g., 'tags', 'signatures', 'multimedia')
     */
    public function getKey(): string;

    /**
     * Check if this aggregator should run for the given context.
     * Allows conditional execution based on editorial type, etc.
     *
     * @param AggregationContext $context The aggregation context
     *
     * @return bool True if this aggregator should execute
     */
    public function supports(AggregationContext $context): bool;

    /**
     * Start async data fetching. Returns promises that will be resolved later.
     *
     * @param AggregationContext $context The aggregation context with editorial data
     *
     * @return array<string, mixed> Promises or immediate data to be resolved
     */
    public function aggregate(AggregationContext $context): array;

    /**
     * Resolve any pending promises and return final data.
     * Called after all aggregators have started their async operations.
     *
     * @param array<string, mixed> $pendingData Data returned from aggregate()
     * @param AggregationContext $context The aggregation context
     *
     * @return array<string, mixed> The resolved data
     */
    public function resolve(array $pendingData, AggregationContext $context): array;

    /**
     * Get the priority of this aggregator.
     * Higher priority aggregators run first (useful for dependencies).
     *
     * @return int The priority (default: 0)
     */
    public function getPriority(): int;

    /**
     * Get list of aggregator keys this aggregator depends on.
     * The dependent aggregators will be resolved before this one.
     *
     * @return array<string> List of aggregator keys
     */
    public function getDependencies(): array;
}
