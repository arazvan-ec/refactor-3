<?php

declare(strict_types=1);

namespace App\Tests\Application\Pipeline;

use App\Application\Aggregator\AggregationContext;
use App\Application\Pipeline\TransformerPipeline;
use App\Application\Transformer\ResponseTransformerInterface;
use App\Application\Transformer\TransformationContext;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Section\Domain\Model\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(TransformerPipeline::class)]
final class TransformerPipelineTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private TransformerPipeline $pipeline;
    private AggregationContext $aggregationContext;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->pipeline = new TransformerPipeline($this->logger);

        $editorialId = $this->createMock(EditorialId::class);
        $editorialId->method('id')->willReturn('editorial-123');

        $editorial = $this->createMock(Editorial::class);
        $editorial->method('id')->willReturn($editorialId);
        $editorial->method('editorialType')->willReturn('article');

        $section = $this->createMock(Section::class);
        $section->method('siteId')->willReturn('site-abc');

        $this->aggregationContext = new AggregationContext($editorial, $section);
    }

    public function testCanAddTransformer(): void
    {
        $transformer = $this->createTransformerMock('test', 0);

        $result = $this->pipeline->addTransformer($transformer);

        self::assertSame($this->pipeline, $result);
        self::assertTrue($this->pipeline->hasTransformer('test'));
    }

    public function testGetRegisteredTransformersReturnsKeys(): void
    {
        $transformer1 = $this->createTransformerMock('trans1', 0);
        $transformer2 = $this->createTransformerMock('trans2', 0);

        $this->pipeline->addTransformer($transformer1);
        $this->pipeline->addTransformer($transformer2);

        $registered = $this->pipeline->getRegisteredTransformers();

        self::assertCount(2, $registered);
        self::assertContains('trans1', $registered);
        self::assertContains('trans2', $registered);
    }

    public function testHasTransformerReturnsFalseWhenNotRegistered(): void
    {
        self::assertFalse($this->pipeline->hasTransformer('nonexistent'));
    }

    public function testTransformCallsAllTransformers(): void
    {
        $transformer1 = $this->createTransformerMock('trans1', 10);
        $transformer1->expects(self::once())
            ->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->setResponseField('field1', 'value1');
            });

        $transformer2 = $this->createTransformerMock('trans2', 5);
        $transformer2->expects(self::once())
            ->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->setResponseField('field2', 'value2');
            });

        $this->pipeline->addTransformer($transformer1);
        $this->pipeline->addTransformer($transformer2);

        $response = $this->pipeline->transform($this->aggregationContext);

        self::assertCount(2, $response);
        self::assertSame('value1', $response['field1']);
        self::assertSame('value2', $response['field2']);
    }

    public function testTransformRespectsPriority(): void
    {
        $executionOrder = [];

        $highPriority = $this->createTransformerMock('high', 100);
        $highPriority->method('transform')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'high';
            });

        $lowPriority = $this->createTransformerMock('low', 10);
        $lowPriority->method('transform')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'low';
            });

        // Add in reverse order to test sorting
        $this->pipeline->addTransformer($lowPriority);
        $this->pipeline->addTransformer($highPriority);

        $this->pipeline->transform($this->aggregationContext);

        self::assertSame(['high', 'low'], $executionOrder);
    }

    public function testSkipsUnsupportedTransformers(): void
    {
        $supported = $this->createTransformerMock('supported', 0);
        $supported->method('supports')->willReturn(true);
        $supported->expects(self::once())
            ->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->setResponseField('data', 'value');
            });

        $unsupported = $this->createTransformerMock('unsupported', 0);
        $unsupported->method('supports')->willReturn(false);
        $unsupported->expects(self::never())->method('transform');

        $this->pipeline->addTransformer($supported);
        $this->pipeline->addTransformer($unsupported);

        $response = $this->pipeline->transform($this->aggregationContext);

        self::assertCount(1, $response);
        self::assertArrayHasKey('data', $response);
    }

    public function testHandlesTransformerExceptionsGracefully(): void
    {
        $failingTransformer = $this->createTransformerMock('failing', 10);
        $failingTransformer->method('transform')
            ->willThrowException(new \RuntimeException('Test error'));

        $workingTransformer = $this->createTransformerMock('working', 5);
        $workingTransformer->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->setResponseField('success', true);
            });

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Transformer failed', self::anything());

        $this->pipeline->addTransformer($failingTransformer);
        $this->pipeline->addTransformer($workingTransformer);

        $response = $this->pipeline->transform($this->aggregationContext);

        self::assertArrayHasKey('success', $response);
    }

    public function testReturnsCombinedResponse(): void
    {
        $trans1 = $this->createTransformerMock('trans1', 0);
        $trans1->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->mergeResponse(['id' => '123', 'title' => 'Test']);
            });

        $trans2 = $this->createTransformerMock('trans2', 0);
        $trans2->method('transform')
            ->willReturnCallback(function (TransformationContext $context) {
                $context->mergeResponse(['body' => ['content'], 'tags' => ['tag1']]);
            });

        $this->pipeline->addTransformer($trans1);
        $this->pipeline->addTransformer($trans2);

        $response = $this->pipeline->transform($this->aggregationContext);

        self::assertCount(4, $response);
        self::assertSame('123', $response['id']);
        self::assertSame('Test', $response['title']);
        self::assertSame(['content'], $response['body']);
        self::assertSame(['tag1'], $response['tags']);
    }

    private function createTransformerMock(string $key, int $priority): ResponseTransformerInterface&MockObject
    {
        $transformer = $this->createMock(ResponseTransformerInterface::class);
        $transformer->method('getKey')->willReturn($key);
        $transformer->method('getPriority')->willReturn($priority);
        $transformer->method('supports')->willReturn(true);

        return $transformer;
    }
}
