<?php

declare(strict_types=1);

namespace App\Tests\Application\Pipeline;

use App\Application\Aggregator\AggregationContext;
use App\Application\Aggregator\AsyncAggregatorInterface;
use App\Application\Pipeline\AggregatorPipeline;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(AggregatorPipeline::class)]
final class AggregatorPipelineTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private AggregatorPipeline $pipeline;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pipeline = new AggregatorPipeline($this->logger);
    }

    public function testCanAddAggregator(): void
    {
        $aggregator = $this->createAggregatorMock('test', 0, []);

        $result = $this->pipeline->addAggregator($aggregator);

        self::assertSame($this->pipeline, $result);
        self::assertTrue($this->pipeline->hasAggregator('test'));
    }

    public function testGetRegisteredAggregatorsReturnsKeys(): void
    {
        $aggregator1 = $this->createAggregatorMock('agg1', 0, []);
        $aggregator2 = $this->createAggregatorMock('agg2', 0, []);

        $this->pipeline->addAggregator($aggregator1);
        $this->pipeline->addAggregator($aggregator2);

        $registered = $this->pipeline->getRegisteredAggregators();

        self::assertCount(2, $registered);
        self::assertContains('agg1', $registered);
        self::assertContains('agg2', $registered);
    }

    public function testHasAggregatorReturnsFalseWhenNotRegistered(): void
    {
        self::assertFalse($this->pipeline->hasAggregator('nonexistent'));
    }

    public function testExecuteCallsAllAggregators(): void
    {
        $context = $this->createMock(AggregationContext::class);

        $aggregator1 = $this->createAggregatorMock('agg1', 10, []);
        $aggregator1->expects(self::once())->method('aggregate')->willReturn(['pending' => true]);
        $aggregator1->expects(self::once())->method('resolve')->willReturn(['resolved' => 'data1']);

        $aggregator2 = $this->createAggregatorMock('agg2', 5, []);
        $aggregator2->expects(self::once())->method('aggregate')->willReturn(['pending' => true]);
        $aggregator2->expects(self::once())->method('resolve')->willReturn(['resolved' => 'data2']);

        $this->pipeline->addAggregator($aggregator1);
        $this->pipeline->addAggregator($aggregator2);

        $results = $this->pipeline->execute($context);

        self::assertCount(2, $results);
        self::assertArrayHasKey('agg1', $results);
        self::assertArrayHasKey('agg2', $results);
    }

    public function testExecuteRespectsPriorityOrder(): void
    {
        $context = $this->createMock(AggregationContext::class);
        $executionOrder = [];

        $highPriority = $this->createAggregatorMock('high', 100, []);
        $highPriority->method('aggregate')->willReturnCallback(function () use (&$executionOrder) {
            $executionOrder[] = 'high';

            return [];
        });
        $highPriority->method('resolve')->willReturn([]);

        $lowPriority = $this->createAggregatorMock('low', 10, []);
        $lowPriority->method('aggregate')->willReturnCallback(function () use (&$executionOrder) {
            $executionOrder[] = 'low';

            return [];
        });
        $lowPriority->method('resolve')->willReturn([]);

        // Add in reverse order to test sorting
        $this->pipeline->addAggregator($lowPriority);
        $this->pipeline->addAggregator($highPriority);

        $this->pipeline->execute($context);

        self::assertSame(['high', 'low'], $executionOrder);
    }

    public function testExecuteRespectsDependencies(): void
    {
        $context = $this->createMock(AggregationContext::class);
        $resolveOrder = [];

        $parent = $this->createAggregatorMock('parent', 50, []);
        $parent->method('aggregate')->willReturn([]);
        $parent->method('resolve')->willReturnCallback(function () use (&$resolveOrder) {
            $resolveOrder[] = 'parent';

            return ['parent' => 'data'];
        });

        $child = $this->createAggregatorMock('child', 50, ['parent']);
        $child->method('aggregate')->willReturn([]);
        $child->method('resolve')->willReturnCallback(function () use (&$resolveOrder) {
            $resolveOrder[] = 'child';

            return ['child' => 'data'];
        });

        $this->pipeline->addAggregator($child);
        $this->pipeline->addAggregator($parent);

        $this->pipeline->execute($context);

        self::assertSame(['parent', 'child'], $resolveOrder);
    }

    public function testExecuteOnlyRunsSpecifiedAggregators(): void
    {
        $context = $this->createMock(AggregationContext::class);

        $included = $this->createAggregatorMock('included', 0, []);
        $included->expects(self::once())->method('aggregate')->willReturn([]);
        $included->expects(self::once())->method('resolve')->willReturn(['data']);

        $excluded = $this->createAggregatorMock('excluded', 0, []);
        $excluded->expects(self::never())->method('aggregate');
        $excluded->expects(self::never())->method('resolve');

        $this->pipeline->addAggregator($included);
        $this->pipeline->addAggregator($excluded);

        $results = $this->pipeline->executeOnly(['included'], $context);

        self::assertCount(1, $results);
        self::assertArrayHasKey('included', $results);
        self::assertArrayNotHasKey('excluded', $results);
    }

    public function testSkipsUnsupportedAggregators(): void
    {
        $context = $this->createMock(AggregationContext::class);

        $supported = $this->createAggregatorMock('supported', 0, []);
        $supported->method('supports')->willReturn(true);
        $supported->expects(self::once())->method('aggregate')->willReturn([]);
        $supported->expects(self::once())->method('resolve')->willReturn(['data']);

        $unsupported = $this->createAggregatorMock('unsupported', 0, []);
        $unsupported->method('supports')->willReturn(false);
        $unsupported->expects(self::never())->method('aggregate');
        $unsupported->expects(self::never())->method('resolve');

        $this->pipeline->addAggregator($supported);
        $this->pipeline->addAggregator($unsupported);

        $results = $this->pipeline->execute($context);

        self::assertCount(1, $results);
        self::assertArrayHasKey('supported', $results);
    }

    public function testHandlesAggregatorExceptionsGracefully(): void
    {
        $context = $this->createMock(AggregationContext::class);

        $failingAggregator = $this->createAggregatorMock('failing', 10, []);
        $failingAggregator->method('aggregate')
            ->willThrowException(new \RuntimeException('Test error'));

        $workingAggregator = $this->createAggregatorMock('working', 5, []);
        $workingAggregator->method('aggregate')->willReturn([]);
        $workingAggregator->method('resolve')->willReturn(['success']);

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Aggregator failed to start', self::anything());

        $this->pipeline->addAggregator($failingAggregator);
        $this->pipeline->addAggregator($workingAggregator);

        $results = $this->pipeline->execute($context);

        self::assertArrayHasKey('working', $results);
    }

    public function testTopologicalSortHandlesComplexDependencies(): void
    {
        $context = $this->createMock(AggregationContext::class);
        $resolveOrder = [];

        // C depends on B, B depends on A
        $aggA = $this->createAggregatorMock('A', 10, []);
        $aggA->method('aggregate')->willReturn([]);
        $aggA->method('resolve')->willReturnCallback(function () use (&$resolveOrder) {
            $resolveOrder[] = 'A';

            return [];
        });

        $aggB = $this->createAggregatorMock('B', 10, ['A']);
        $aggB->method('aggregate')->willReturn([]);
        $aggB->method('resolve')->willReturnCallback(function () use (&$resolveOrder) {
            $resolveOrder[] = 'B';

            return [];
        });

        $aggC = $this->createAggregatorMock('C', 10, ['B']);
        $aggC->method('aggregate')->willReturn([]);
        $aggC->method('resolve')->willReturnCallback(function () use (&$resolveOrder) {
            $resolveOrder[] = 'C';

            return [];
        });

        // Add in reverse dependency order
        $this->pipeline->addAggregator($aggC);
        $this->pipeline->addAggregator($aggB);
        $this->pipeline->addAggregator($aggA);

        $this->pipeline->execute($context);

        self::assertSame(['A', 'B', 'C'], $resolveOrder);
    }

    public function testExecuteOnlyLogsWarningForUnknownAggregator(): void
    {
        $context = $this->createMock(AggregationContext::class);

        $this->logger->expects(self::once())
            ->method('warning')
            ->with('Aggregator not found', ['aggregator' => 'unknown']);

        $results = $this->pipeline->executeOnly(['unknown'], $context);

        self::assertEmpty($results);
    }

    /**
     * @param array<string> $dependencies
     */
    private function createAggregatorMock(
        string $key,
        int $priority,
        array $dependencies
    ): AsyncAggregatorInterface&MockObject {
        $aggregator = $this->createMock(AsyncAggregatorInterface::class);
        $aggregator->method('getKey')->willReturn($key);
        $aggregator->method('getPriority')->willReturn($priority);
        $aggregator->method('getDependencies')->willReturn($dependencies);
        $aggregator->method('supports')->willReturn(true);

        return $aggregator;
    }
}
