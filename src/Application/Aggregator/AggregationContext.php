<?php

declare(strict_types=1);

namespace App\Application\Aggregator;

use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Section\Domain\Model\Section;

/**
 * Context object passed to all aggregators.
 * Contains all the data needed for aggregation and allows sharing data between aggregators.
 *
 * Immutable for editorial/section, mutable for resolved data.
 */
final class AggregationContext
{
    /** @var array<string, mixed> Shared data between aggregators */
    private array $sharedData = [];

    /** @var array<string, array<string, mixed>> Resolved data from aggregators */
    private array $resolvedData = [];

    /** @var array<string, mixed> Promises pending resolution */
    private array $pendingPromises = [];

    public function __construct(
        private readonly NewsBase $editorial,
        private readonly Section $section,
    ) {
    }

    public function getEditorial(): NewsBase
    {
        return $this->editorial;
    }

    /**
     * Get editorial as Editorial type (for type-safe access to specific methods).
     */
    public function getEditorialAsEditorial(): Editorial
    {
        /** @var Editorial $editorial */
        $editorial = $this->editorial;

        return $editorial;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function getEditorialId(): string
    {
        return $this->editorial->id()->id();
    }

    public function getSiteId(): string
    {
        return $this->section->siteId();
    }

    public function getEditorialType(): string
    {
        return $this->editorial->editorialType();
    }

    /**
     * Set shared data that can be accessed by other aggregators.
     *
     * @param array<string, mixed> $data
     */
    public function setSharedData(string $key, array $data): self
    {
        $this->sharedData[$key] = $data;

        return $this;
    }

    /**
     * Get shared data from another aggregator.
     *
     * @return array<string, mixed>
     */
    public function getSharedData(string $key): array
    {
        return $this->sharedData[$key] ?? [];
    }

    /**
     * Check if shared data exists.
     */
    public function hasSharedData(string $key): bool
    {
        return isset($this->sharedData[$key]);
    }

    /**
     * Store resolved data from an aggregator.
     *
     * @param array<string, mixed> $data
     */
    public function setResolvedData(string $aggregatorKey, array $data): self
    {
        $this->resolvedData[$aggregatorKey] = $data;

        return $this;
    }

    /**
     * Get resolved data from a specific aggregator.
     *
     * @return array<string, mixed>
     */
    public function getResolvedData(string $aggregatorKey): array
    {
        return $this->resolvedData[$aggregatorKey] ?? [];
    }

    /**
     * Get all resolved data.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllResolvedData(): array
    {
        return $this->resolvedData;
    }

    /**
     * Add pending promises to be resolved later.
     *
     * @param array<string, mixed> $promises
     */
    public function addPendingPromises(string $key, array $promises): self
    {
        if (!isset($this->pendingPromises[$key])) {
            $this->pendingPromises[$key] = [];
        }
        $this->pendingPromises[$key] = array_merge($this->pendingPromises[$key], $promises);

        return $this;
    }

    /**
     * Get pending promises for a key.
     *
     * @return array<string, mixed>
     */
    public function getPendingPromises(string $key): array
    {
        return $this->pendingPromises[$key] ?? [];
    }

    /**
     * Get all pending promises.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllPendingPromises(): array
    {
        return $this->pendingPromises;
    }

    /**
     * Clear pending promises for a key after resolution.
     */
    public function clearPendingPromises(string $key): self
    {
        unset($this->pendingPromises[$key]);

        return $this;
    }
}
