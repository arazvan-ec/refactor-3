<?php

declare(strict_types=1);

namespace App\Tests\Application\Aggregator\Impl;

use App\Application\Aggregator\AggregationContext;
use App\Application\Aggregator\Impl\TagsAggregator;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Editorial\Domain\Model\Tag as EditorialTag;
use Ec\Editorial\Domain\Model\Tags;
use Ec\Section\Domain\Model\Section;
use Ec\Tag\Domain\Model\QueryTagClient;
use Ec\Tag\Domain\Model\Tag;
use Ec\Tag\Domain\Model\TagId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(TagsAggregator::class)]
final class TagsAggregatorTest extends TestCase
{
    private QueryTagClient&MockObject $queryTagClient;
    private LoggerInterface&MockObject $logger;
    private TagsAggregator $aggregator;

    protected function setUp(): void
    {
        $this->queryTagClient = $this->createMock(QueryTagClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->aggregator = new TagsAggregator(
            $this->queryTagClient,
            $this->logger
        );
    }

    public function testGetKeyReturnsTags(): void
    {
        self::assertSame('tags', $this->aggregator->getKey());
    }

    public function testGetPriorityReturns100(): void
    {
        self::assertSame(100, $this->aggregator->getPriority());
    }

    public function testGetDependenciesReturnsEmptyArray(): void
    {
        self::assertSame([], $this->aggregator->getDependencies());
    }

    public function testAggregateReturnsTagIds(): void
    {
        $tagId1 = $this->createMock(EditorialTag::class);
        $tagId1->method('id')->willReturn('tag-1');

        $tagId2 = $this->createMock(EditorialTag::class);
        $tagId2->method('id')->willReturn('tag-2');

        $tags = $this->createMock(Tags::class);
        $tags->method('getArrayCopy')->willReturn([$tagId1, $tagId2]);

        $editorial = $this->createEditorialMock($tags);
        $context = $this->createContext($editorial);

        $result = $this->aggregator->aggregate($context);

        self::assertArrayHasKey('tagIds', $result);
        self::assertCount(2, $result['tagIds']);
    }

    public function testResolveReturnsFetchedTags(): void
    {
        $tagId = $this->createMock(EditorialTag::class);
        $tagId->method('id')->willReturn('tag-1');

        $fetchedTag = $this->createMock(Tag::class);
        $this->queryTagClient->method('findTagById')
            ->with('tag-1')
            ->willReturn($fetchedTag);

        $tags = $this->createMock(Tags::class);
        $tags->method('getArrayCopy')->willReturn([]);

        $editorial = $this->createEditorialMock($tags);
        $context = $this->createContext($editorial);

        $pendingData = ['tagIds' => [$tagId]];
        $result = $this->aggregator->resolve($pendingData, $context);

        self::assertArrayHasKey('tags', $result);
        self::assertCount(1, $result['tags']);
        self::assertSame($fetchedTag, $result['tags'][0]);
    }

    public function testResolveReturnsEmptyOnEmptyTagIds(): void
    {
        $tags = $this->createMock(Tags::class);
        $tags->method('getArrayCopy')->willReturn([]);

        $editorial = $this->createEditorialMock($tags);
        $context = $this->createContext($editorial);

        $pendingData = ['tagIds' => []];
        $result = $this->aggregator->resolve($pendingData, $context);

        self::assertSame(['tags' => []], $result);
    }

    public function testResolveHandlesFetchErrorsGracefully(): void
    {
        $tagId = $this->createMock(EditorialTag::class);
        $tagId->method('id')->willReturn('tag-1');

        $this->queryTagClient->method('findTagById')
            ->willThrowException(new \RuntimeException('Not found'));

        $this->logger->expects(self::once())
            ->method('warning')
            ->with('Failed to fetch tag', self::anything());

        $tags = $this->createMock(Tags::class);
        $tags->method('getArrayCopy')->willReturn([]);

        $editorial = $this->createEditorialMock($tags);
        $context = $this->createContext($editorial);

        $pendingData = ['tagIds' => [$tagId]];
        $result = $this->aggregator->resolve($pendingData, $context);

        self::assertSame(['tags' => []], $result);
    }

    private function createEditorialMock(Tags $tags): Editorial&MockObject
    {
        $editorialId = $this->createMock(EditorialId::class);
        $editorialId->method('id')->willReturn('editorial-123');

        $editorial = $this->createMock(Editorial::class);
        $editorial->method('id')->willReturn($editorialId);
        $editorial->method('tags')->willReturn($tags);
        $editorial->method('editorialType')->willReturn('article');

        return $editorial;
    }

    private function createContext(Editorial $editorial): AggregationContext
    {
        $section = $this->createMock(Section::class);
        $section->method('siteId')->willReturn('site-abc');

        return new AggregationContext($editorial, $section);
    }
}
