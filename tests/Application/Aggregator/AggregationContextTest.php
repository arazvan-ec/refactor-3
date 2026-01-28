<?php

declare(strict_types=1);

namespace App\Tests\Application\Aggregator;

use App\Application\Aggregator\AggregationContext;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Section\Domain\Model\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AggregationContext::class)]
final class AggregationContextTest extends TestCase
{
    private NewsBase&MockObject $editorial;
    private Section&MockObject $section;
    private AggregationContext $context;

    protected function setUp(): void
    {
        $editorialId = $this->createMock(EditorialId::class);
        $editorialId->method('id')->willReturn('editorial-123');

        $this->editorial = $this->createMock(Editorial::class);
        $this->editorial->method('id')->willReturn($editorialId);
        $this->editorial->method('editorialType')->willReturn('article');

        $this->section = $this->createMock(Section::class);
        $this->section->method('siteId')->willReturn('site-abc');

        $this->context = new AggregationContext($this->editorial, $this->section);
    }

    public function testCanBeCreatedWithEditorialAndSection(): void
    {
        self::assertInstanceOf(AggregationContext::class, $this->context);
    }

    public function testGetEditorialReturnsEditorial(): void
    {
        self::assertSame($this->editorial, $this->context->getEditorial());
    }

    public function testGetSectionReturnsSection(): void
    {
        self::assertSame($this->section, $this->context->getSection());
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

    public function testSharedDataCanBeSetAndRetrieved(): void
    {
        $data = ['key' => 'value', 'count' => 42];

        $result = $this->context->setSharedData('testKey', $data);

        self::assertSame($this->context, $result);
        self::assertSame($data, $this->context->getSharedData('testKey'));
    }

    public function testGetSharedDataReturnsEmptyArrayWhenNotSet(): void
    {
        self::assertSame([], $this->context->getSharedData('nonexistent'));
    }

    public function testHasSharedDataReturnsTrueWhenSet(): void
    {
        $this->context->setSharedData('existingKey', ['data']);

        self::assertTrue($this->context->hasSharedData('existingKey'));
    }

    public function testHasSharedDataReturnsFalseWhenNotSet(): void
    {
        self::assertFalse($this->context->hasSharedData('nonexistent'));
    }

    public function testResolvedDataCanBeSetAndRetrieved(): void
    {
        $data = ['resolved' => 'data'];

        $result = $this->context->setResolvedData('aggregatorKey', $data);

        self::assertSame($this->context, $result);
        self::assertSame($data, $this->context->getResolvedData('aggregatorKey'));
    }

    public function testGetResolvedDataReturnsEmptyArrayWhenNotSet(): void
    {
        self::assertSame([], $this->context->getResolvedData('nonexistent'));
    }

    public function testGetAllResolvedDataReturnsAllData(): void
    {
        $this->context->setResolvedData('key1', ['data1']);
        $this->context->setResolvedData('key2', ['data2']);

        $allData = $this->context->getAllResolvedData();

        self::assertCount(2, $allData);
        self::assertSame(['data1'], $allData['key1']);
        self::assertSame(['data2'], $allData['key2']);
    }

    public function testPendingPromisesCanBeAddedAndRetrieved(): void
    {
        $promises = ['promise1', 'promise2'];

        $result = $this->context->addPendingPromises('aggregator', $promises);

        self::assertSame($this->context, $result);
        self::assertSame($promises, $this->context->getPendingPromises('aggregator'));
    }

    public function testAddPendingPromisesMergesWithExisting(): void
    {
        $this->context->addPendingPromises('aggregator', ['promise1']);
        $this->context->addPendingPromises('aggregator', ['promise2']);

        $allPromises = $this->context->getPendingPromises('aggregator');

        self::assertCount(2, $allPromises);
        self::assertContains('promise1', $allPromises);
        self::assertContains('promise2', $allPromises);
    }

    public function testGetPendingPromisesReturnsEmptyArrayWhenNotSet(): void
    {
        self::assertSame([], $this->context->getPendingPromises('nonexistent'));
    }

    public function testGetAllPendingPromisesReturnsAllPromises(): void
    {
        $this->context->addPendingPromises('key1', ['p1']);
        $this->context->addPendingPromises('key2', ['p2']);

        $all = $this->context->getAllPendingPromises();

        self::assertCount(2, $all);
    }

    public function testClearPendingPromisesRemovesPromisesForKey(): void
    {
        $this->context->addPendingPromises('aggregator', ['promise']);

        $result = $this->context->clearPendingPromises('aggregator');

        self::assertSame($this->context, $result);
        self::assertSame([], $this->context->getPendingPromises('aggregator'));
    }
}
