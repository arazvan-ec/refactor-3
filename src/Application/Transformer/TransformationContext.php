<?php

declare(strict_types=1);

namespace App\Application\Transformer;

use App\Application\Aggregator\AggregationContext;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Section\Domain\Model\Section;

/**
 * Context object for transformation phase.
 * Provides access to aggregated data and builds the response.
 */
final class TransformationContext
{
    /** @var array<string, mixed> The response being built */
    private array $response = [];

    public function __construct(
        private readonly AggregationContext $aggregationContext,
    ) {
    }

    /**
     * Get the underlying aggregation context.
     */
    public function getAggregationContext(): AggregationContext
    {
        return $this->aggregationContext;
    }

    /**
     * Get the editorial from the aggregation context.
     */
    public function getEditorial(): NewsBase
    {
        return $this->aggregationContext->getEditorial();
    }

    /**
     * Get the section from the aggregation context.
     */
    public function getSection(): Section
    {
        return $this->aggregationContext->getSection();
    }

    /**
     * Get aggregated data by key.
     *
     * @return array<string, mixed>
     */
    public function getAggregatedData(string $key): array
    {
        return $this->aggregationContext->getResolvedData($key);
    }

    /**
     * Get all aggregated data.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllAggregatedData(): array
    {
        return $this->aggregationContext->getAllResolvedData();
    }

    /**
     * Check if aggregated data exists for a key.
     */
    public function hasAggregatedData(string $key): bool
    {
        return !empty($this->aggregationContext->getResolvedData($key));
    }

    /**
     * Set a single response field.
     */
    public function setResponseField(string $key, mixed $value): self
    {
        $this->response[$key] = $value;

        return $this;
    }

    /**
     * Get a single response field.
     */
    public function getResponseField(string $key): mixed
    {
        return $this->response[$key] ?? null;
    }

    /**
     * Check if a response field exists.
     */
    public function hasResponseField(string $key): bool
    {
        return isset($this->response[$key]);
    }

    /**
     * Merge data into the response.
     *
     * @param array<string, mixed> $data
     */
    public function mergeResponse(array $data): self
    {
        $this->response = array_merge($this->response, $data);

        return $this;
    }

    /**
     * Get the complete response.
     *
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * Get the editorial ID.
     */
    public function getEditorialId(): string
    {
        return $this->aggregationContext->getEditorialId();
    }

    /**
     * Get the site ID.
     */
    public function getSiteId(): string
    {
        return $this->aggregationContext->getSiteId();
    }

    /**
     * Get the editorial type.
     */
    public function getEditorialType(): string
    {
        return $this->aggregationContext->getEditorialType();
    }
}
