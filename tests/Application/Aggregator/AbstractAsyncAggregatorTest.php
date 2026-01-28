<?php

declare(strict_types=1);

namespace App\Tests\Application\Aggregator;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(AbstractAsyncAggregator::class)]
final class AbstractAsyncAggregatorTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private TestableAggregator $aggregator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->aggregator = new TestableAggregator($this->logger);
    }

    public function testDefaultPriorityIsZero(): void
    {
        self::assertSame(0, $this->aggregator->getPriority());
    }

    public function testDefaultDependenciesIsEmpty(): void
    {
        self::assertSame([], $this->aggregator->getDependencies());
    }

    public function testDefaultSupportsReturnsTrue(): void
    {
        $context = $this->createMock(AggregationContext::class);

        self::assertTrue($this->aggregator->supports($context));
    }

    public function testResolvePromisesReturnsFulfilledValues(): void
    {
        $promises = [
            'key1' => new FulfilledPromise('value1'),
            'key2' => new FulfilledPromise('value2'),
        ];

        $result = $this->aggregator->callResolvePromises($promises);

        self::assertArrayHasKey('key1', $result);
        self::assertArrayHasKey('key2', $result);
        self::assertSame('value1', $result['key1']);
        self::assertSame('value2', $result['key2']);
    }

    public function testResolvePromisesLogsRejectedPromises(): void
    {
        $exception = new \RuntimeException('Test error');
        $promises = [
            'key1' => new FulfilledPromise('value1'),
            'key2' => new RejectedPromise($exception),
        ];

        $this->logger->expects(self::once())
            ->method('warning')
            ->with('Promise rejected', self::callback(function ($context) {
                return 'test' === $context['aggregator']
                    && 'key2' === $context['key']
                    && 'Test error' === $context['reason'];
            }));

        $result = $this->aggregator->callResolvePromises($promises);

        self::assertCount(1, $result);
        self::assertArrayHasKey('key1', $result);
        self::assertArrayNotHasKey('key2', $result);
    }

    public function testResolvePromisesReturnsEmptyArrayOnEmptyInput(): void
    {
        $result = $this->aggregator->callResolvePromises([]);

        self::assertSame([], $result);
    }

    public function testSafeExecuteReturnsCallbackResult(): void
    {
        $callback = fn () => 'success';

        $result = $this->aggregator->callSafeExecute($callback);

        self::assertSame('success', $result);
    }

    public function testSafeExecuteReturnsDefaultOnException(): void
    {
        $callback = fn () => throw new \RuntimeException('Test error');

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Aggregator execution failed', self::callback(function ($context) {
                return 'test' === $context['aggregator'] && 'Test error' === $context['error'];
            }));

        $result = $this->aggregator->callSafeExecute($callback, 'default');

        self::assertSame('default', $result);
    }

    public function testSafeExecuteReturnsNullAsDefaultOnException(): void
    {
        $callback = fn () => throw new \RuntimeException('Test error');
        $this->logger->expects(self::once())->method('error');

        $result = $this->aggregator->callSafeExecute($callback);

        self::assertNull($result);
    }
}

/**
 * Testable implementation to expose protected methods.
 */
final class TestableAggregator extends AbstractAsyncAggregator
{
    public function getKey(): string
    {
        return 'test';
    }

    /**
     * @param array<string, mixed> $pendingData
     *
     * @return array<string, mixed>
     */
    public function aggregate(AggregationContext $context): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $pendingData
     *
     * @return array<string, mixed>
     */
    public function resolve(array $pendingData, AggregationContext $context): array
    {
        return [];
    }

    /**
     * @param array<int|string, mixed> $promises
     *
     * @return array<string, mixed>
     */
    public function callResolvePromises(array $promises): array
    {
        return $this->resolvePromises($promises);
    }

    /**
     * @template T
     *
     * @param callable(): T $callback
     * @param T $default
     *
     * @return T
     */
    public function callSafeExecute(callable $callback, mixed $default = null): mixed
    {
        return $this->safeExecute($callback, $default);
    }
}
