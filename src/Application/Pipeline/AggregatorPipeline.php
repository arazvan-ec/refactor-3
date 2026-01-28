<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Application\Aggregator\AggregationContext;
use App\Application\Aggregator\AsyncAggregatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Pipeline that coordinates execution of all registered aggregators.
 *
 * Features:
 * - Automatic dependency resolution
 * - Priority-based execution order
 * - Parallel execution of independent aggregators
 * - Shared context between aggregators
 *
 * Adding a new aggregator:
 * 1. Create class implementing AsyncAggregatorInterface
 * 2. Tag with 'app.aggregator'
 * 3. Pipeline automatically includes it
 */
final class AggregatorPipeline
{
    /** @var array<string, AsyncAggregatorInterface> */
    private array $aggregators = [];

    /** @var array<string, AsyncAggregatorInterface>|null Sorted aggregators cache */
    private ?array $sortedAggregators = null;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Register an aggregator.
     */
    public function addAggregator(AsyncAggregatorInterface $aggregator): self
    {
        $this->aggregators[$aggregator->getKey()] = $aggregator;
        $this->sortedAggregators = null; // Invalidate cache

        return $this;
    }

    /**
     * Execute all aggregators and return combined results.
     *
     * @return array<string, mixed> Combined results from all aggregators
     */
    public function execute(AggregationContext $context): array
    {
        $sortedAggregators = $this->getSortedAggregators();
        $pendingData = [];

        // Phase 1: Start all async operations
        foreach ($sortedAggregators as $key => $aggregator) {
            if (!$aggregator->supports($context)) {
                $this->logger->debug('Aggregator skipped (not supported)', ['aggregator' => $key]);
                continue;
            }

            try {
                $pendingData[$key] = $aggregator->aggregate($context);
                $this->logger->debug('Aggregator started', ['aggregator' => $key]);
            } catch (\Throwable $e) {
                $this->logger->error('Aggregator failed to start', [
                    'aggregator' => $key,
                    'error' => $e->getMessage(),
                ]);
                $pendingData[$key] = [];
            }
        }

        // Phase 2: Resolve all pending data respecting dependencies
        $results = [];
        $resolved = [];

        foreach ($sortedAggregators as $key => $aggregator) {
            if (!isset($pendingData[$key])) {
                continue;
            }

            // Wait for dependencies
            foreach ($aggregator->getDependencies() as $dependency) {
                if (!isset($resolved[$dependency]) && isset($pendingData[$dependency])) {
                    $this->resolveAggregator(
                        $dependency,
                        $sortedAggregators[$dependency],
                        $pendingData[$dependency],
                        $context,
                        $results,
                        $resolved
                    );
                }
            }

            // Resolve this aggregator
            $this->resolveAggregator(
                $key,
                $aggregator,
                $pendingData[$key],
                $context,
                $results,
                $resolved
            );
        }

        return $results;
    }

    /**
     * Execute only specific aggregators.
     *
     * @param array<string> $keys Aggregator keys to execute
     *
     * @return array<string, mixed> Results from specified aggregators
     */
    public function executeOnly(array $keys, AggregationContext $context): array
    {
        $results = [];

        foreach ($keys as $key) {
            if (!isset($this->aggregators[$key])) {
                $this->logger->warning('Aggregator not found', ['aggregator' => $key]);
                continue;
            }

            $aggregator = $this->aggregators[$key];

            if (!$aggregator->supports($context)) {
                continue;
            }

            try {
                $pendingData = $aggregator->aggregate($context);
                $resolved = $aggregator->resolve($pendingData, $context);
                $context->setResolvedData($key, $resolved);
                $results[$key] = $resolved;
            } catch (\Throwable $e) {
                $this->logger->error('Aggregator execution failed', [
                    'aggregator' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Get list of registered aggregator keys.
     *
     * @return array<string>
     */
    public function getRegisteredAggregators(): array
    {
        return array_keys($this->aggregators);
    }

    /**
     * Check if an aggregator is registered.
     */
    public function hasAggregator(string $key): bool
    {
        return isset($this->aggregators[$key]);
    }

    /**
     * @param array<string, mixed> $pendingData
     * @param array<string, mixed> $results
     * @param array<string, bool> $resolved
     */
    private function resolveAggregator(
        string $key,
        AsyncAggregatorInterface $aggregator,
        array $pendingData,
        AggregationContext $context,
        array &$results,
        array &$resolved
    ): void {
        if (isset($resolved[$key])) {
            return;
        }

        try {
            $result = $aggregator->resolve($pendingData, $context);
            $context->setResolvedData($key, $result);
            $results[$key] = $result;
            $resolved[$key] = true;

            $this->logger->debug('Aggregator resolved', ['aggregator' => $key]);
        } catch (\Throwable $e) {
            $this->logger->error('Aggregator failed to resolve', [
                'aggregator' => $key,
                'error' => $e->getMessage(),
            ]);
            $resolved[$key] = true;
        }
    }

    /**
     * Sort aggregators by priority and dependencies.
     *
     * @return array<string, AsyncAggregatorInterface>
     */
    private function getSortedAggregators(): array
    {
        if (null !== $this->sortedAggregators) {
            return $this->sortedAggregators;
        }

        $aggregators = $this->aggregators;

        // Sort by priority (higher first)
        uasort($aggregators, static function (AsyncAggregatorInterface $a, AsyncAggregatorInterface $b): int {
            return $b->getPriority() <=> $a->getPriority();
        });

        // Topological sort for dependencies
        $this->sortedAggregators = $this->topologicalSort($aggregators);

        return $this->sortedAggregators;
    }

    /**
     * Topological sort of aggregators based on dependencies.
     *
     * @param array<string, AsyncAggregatorInterface> $aggregators
     *
     * @return array<string, AsyncAggregatorInterface>
     */
    private function topologicalSort(array $aggregators): array
    {
        $sorted = [];
        $visited = [];

        foreach ($aggregators as $key => $aggregator) {
            $this->visitAggregator($key, $aggregators, $sorted, $visited);
        }

        return $sorted;
    }

    /**
     * @param array<string, AsyncAggregatorInterface> $aggregators
     * @param array<string, AsyncAggregatorInterface> $sorted
     * @param array<string, bool> $visited
     */
    private function visitAggregator(
        string $key,
        array $aggregators,
        array &$sorted,
        array &$visited
    ): void {
        if (isset($visited[$key])) {
            return;
        }

        $visited[$key] = true;

        if (!isset($aggregators[$key])) {
            return;
        }

        $aggregator = $aggregators[$key];

        foreach ($aggregator->getDependencies() as $dependency) {
            $this->visitAggregator($dependency, $aggregators, $sorted, $visited);
        }

        $sorted[$key] = $aggregator;
    }
}
