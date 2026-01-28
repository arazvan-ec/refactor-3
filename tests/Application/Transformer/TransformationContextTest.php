<?php

declare(strict_types=1);

namespace App\Tests\Application\Transformer;

use App\Application\Aggregator\AggregationContext;
use App\Application\Transformer\TransformationContext;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Section\Domain\Model\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformationContext::class)]
final class TransformationContextTest extends TestCase
{
    private AggregationContext&MockObject $aggregationContext;
    private TransformationContext $context;

    protected function setUp(): void
    {
        $editorialId = $this->createMock(EditorialId::class);
        $editorialId->method('id')->willReturn('editorial-123');

        $editorial = $this->createMock(Editorial::class);
        $editorial->method('id')->willReturn($editorialId);
        $editorial->method('editorialType')->willReturn('article');

        $section = $this->createMock(Section::class);
        $section->method('siteId')->willReturn('site-abc');

        $this->aggregationContext = new AggregationContext($editorial, $section);
        $this->aggregationContext->setResolvedData('tags', ['tag1', 'tag2']);

        $this->context = new TransformationContext($this->aggregationContext);
    }

    public function testCanAccessAggregationContext(): void
    {
        self::assertSame($this->aggregationContext, $this->context->getAggregationContext());
    }

    public function testCanGetEditorial(): void
    {
        self::assertSame(
            $this->aggregationContext->getEditorial(),
            $this->context->getEditorial()
        );
    }

    public function testCanGetSection(): void
    {
        self::assertSame(
            $this->aggregationContext->getSection(),
            $this->context->getSection()
        );
    }

    public function testCanGetAggregatedDataByKey(): void
    {
        $data = $this->context->getAggregatedData('tags');

        self::assertSame(['tag1', 'tag2'], $data);
    }

    public function testGetAggregatedDataReturnsEmptyForMissingKey(): void
    {
        $data = $this->context->getAggregatedData('nonexistent');

        self::assertSame([], $data);
    }

    public function testHasAggregatedDataReturnsTrueWhenExists(): void
    {
        self::assertTrue($this->context->hasAggregatedData('tags'));
    }

    public function testHasAggregatedDataReturnsFalseWhenMissing(): void
    {
        self::assertFalse($this->context->hasAggregatedData('nonexistent'));
    }

    public function testCanSetResponseField(): void
    {
        $result = $this->context->setResponseField('title', 'Test Title');

        self::assertSame($this->context, $result);
        self::assertSame('Test Title', $this->context->getResponseField('title'));
    }

    public function testGetResponseFieldReturnsNullForMissing(): void
    {
        self::assertNull($this->context->getResponseField('nonexistent'));
    }

    public function testHasResponseFieldReturnsTrueWhenSet(): void
    {
        $this->context->setResponseField('field', 'value');

        self::assertTrue($this->context->hasResponseField('field'));
    }

    public function testHasResponseFieldReturnsFalseWhenMissing(): void
    {
        self::assertFalse($this->context->hasResponseField('nonexistent'));
    }

    public function testCanMergeResponse(): void
    {
        $this->context->setResponseField('existing', 'value');
        $result = $this->context->mergeResponse(['new1' => 'data1', 'new2' => 'data2']);

        self::assertSame($this->context, $result);

        $response = $this->context->getResponse();
        self::assertArrayHasKey('existing', $response);
        self::assertArrayHasKey('new1', $response);
        self::assertArrayHasKey('new2', $response);
    }

    public function testGetResponseReturnsAllFields(): void
    {
        $this->context->setResponseField('field1', 'value1');
        $this->context->setResponseField('field2', 'value2');

        $response = $this->context->getResponse();

        self::assertCount(2, $response);
        self::assertSame('value1', $response['field1']);
        self::assertSame('value2', $response['field2']);
    }

    public function testGetEditorialIdReturnsId(): void
    {
        self::assertSame('editorial-123', $this->context->getEditorialId());
    }

    public function testGetSiteIdReturnsSiteId(): void
    {
        self::assertSame('site-abc', $this->context->getSiteId());
    }

    public function testGetEditorialTypeReturnsType(): void
    {
        self::assertSame('article', $this->context->getEditorialType());
    }
}
