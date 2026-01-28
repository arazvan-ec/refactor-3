<?php

declare(strict_types=1);

namespace App\Application\Aggregator;

use GuzzleHttp\Promise\Utils;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for async aggregators.
 * Provides common functionality for promise handling and error logging.
 */
abstract class AbstractAsyncAggregator implements AsyncAggregatorInterface
{
    protected const UNWRAPPED = true;

    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function supports(AggregationContext $context): bool
    {
        return true;
    }

    /**
     * Resolve an array of promises and return fulfilled results.
     *
     * @param array<int|string, Promise|mixed> $promises
     *
     * @return array<string, mixed>
     */
    protected function resolvePromises(array $promises): array
    {
        if (empty($promises)) {
            return [];
        }

        try {
            return Utils::settle($promises)
                ->then(fn (array $settled) => $this->extractFulfilledPromises($settled))
                ->wait(self::UNWRAPPED);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to resolve promises', [
                'aggregator' => $this->getKey(),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract fulfilled values from settled promises.
     *
     * @param array<string, array{state: string, value?: mixed, reason?: \Throwable}> $settled
     *
     * @return array<string, mixed>
     */
    protected function extractFulfilledPromises(array $settled): array
    {
        $result = [];

        foreach ($settled as $key => $promise) {
            if (Promise::FULFILLED === $promise['state'] && isset($promise['value'])) {
                $result[$key] = $promise['value'];
            } elseif (Promise::REJECTED === $promise['state'] && isset($promise['reason'])) {
                $this->logger->warning('Promise rejected', [
                    'aggregator' => $this->getKey(),
                    'key' => $key,
                    'reason' => $promise['reason']->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * Safely execute a callback, logging any errors.
     *
     * @template T
     *
     * @param callable(): T $callback
     * @param T $default
     *
     * @return T
     */
    protected function safeExecute(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $this->logger->error('Aggregator execution failed', [
                'aggregator' => $this->getKey(),
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }
}
