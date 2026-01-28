<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Application\Aggregator\AggregationContext;
use App\Application\Transformer\ResponseTransformerInterface;
use App\Application\Transformer\TransformationContext;
use Psr\Log\LoggerInterface;

/**
 * Pipeline that coordinates execution of all registered transformers.
 *
 * Features:
 * - Priority-based execution order
 * - Shared context between transformers
 * - Graceful error handling
 *
 * Adding a new transformer:
 * 1. Create class implementing ResponseTransformerInterface
 * 2. Tag with 'app.response_transformer'
 * 3. Pipeline automatically includes it
 */
final class TransformerPipeline
{
    /** @var array<string, ResponseTransformerInterface> */
    private array $transformers = [];

    /** @var array<string, ResponseTransformerInterface>|null Sorted transformers cache */
    private ?array $sortedTransformers = null;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Register a transformer.
     */
    public function addTransformer(ResponseTransformerInterface $transformer): self
    {
        $this->transformers[$transformer->getKey()] = $transformer;
        $this->sortedTransformers = null; // Invalidate cache

        return $this;
    }

    /**
     * Transform aggregated data into response.
     *
     * @param AggregationContext $aggregationContext Context with aggregated data
     *
     * @return array<string, mixed> Final JSON response
     */
    public function transform(AggregationContext $aggregationContext): array
    {
        $context = new TransformationContext($aggregationContext);
        $sortedTransformers = $this->getSortedTransformers();

        foreach ($sortedTransformers as $key => $transformer) {
            if (!$transformer->supports($context)) {
                $this->logger->debug('Transformer skipped (not supported)', ['transformer' => $key]);
                continue;
            }

            try {
                $transformer->transform($context);
                $this->logger->debug('Transformer executed', ['transformer' => $key]);
            } catch (\Throwable $e) {
                $this->logger->error('Transformer failed', [
                    'transformer' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $context->getResponse();
    }

    /**
     * Get list of registered transformer keys.
     *
     * @return array<string>
     */
    public function getRegisteredTransformers(): array
    {
        return array_keys($this->transformers);
    }

    /**
     * Check if a transformer is registered.
     */
    public function hasTransformer(string $key): bool
    {
        return isset($this->transformers[$key]);
    }

    /**
     * Sort transformers by priority (higher first).
     *
     * @return array<string, ResponseTransformerInterface>
     */
    private function getSortedTransformers(): array
    {
        if (null !== $this->sortedTransformers) {
            return $this->sortedTransformers;
        }

        $transformers = $this->transformers;

        uasort($transformers, static function (ResponseTransformerInterface $a, ResponseTransformerInterface $b): int {
            return $b->getPriority() <=> $a->getPriority();
        });

        $this->sortedTransformers = $transformers;

        return $this->sortedTransformers;
    }
}
