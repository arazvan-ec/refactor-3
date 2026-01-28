<?php

declare(strict_types=1);

namespace App\Application\Transformer;

use Psr\Log\LoggerInterface;

/**
 * Abstract base class for response transformers.
 * Provides common functionality for error handling and logging.
 */
abstract class AbstractResponseTransformer implements ResponseTransformerInterface
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function supports(TransformationContext $context): bool
    {
        return true;
    }

    /**
     * Safely execute a transformation, logging any errors.
     *
     * @param callable(): void $callback
     */
    protected function safeTransform(TransformationContext $context, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            $this->logger->error('Transformer execution failed', [
                'transformer' => $this->getKey(),
                'editorial' => $context->getEditorialId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get value from aggregated data with fallback.
     *
     * @param array<string, mixed> $data
     */
    protected function getValue(array $data, string $key, mixed $default = null): mixed
    {
        return $data[$key] ?? $default;
    }
}
