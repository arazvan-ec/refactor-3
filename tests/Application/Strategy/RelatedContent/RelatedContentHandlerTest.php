<?php

declare(strict_types=1);

namespace App\Tests\Application\Strategy\RelatedContent;

use App\Application\Strategy\RelatedContent\RelatedContentHandler;
use App\Application\Strategy\RelatedContent\RelatedContentStrategyInterface;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Section\Domain\Model\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelatedContentHandler::class)]
final class RelatedContentHandlerTest extends TestCase
{
    private RelatedContentHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new RelatedContentHandler();
    }

    public function testAddStrategyRegistersStrategy(): void
    {
        $strategy = $this->createMockStrategy('testType');

        $this->handler->addStrategy($strategy);

        self::assertTrue($this->handler->hasStrategy('testType'));
    }

    public function testHasStrategyReturnsFalseForUnknownType(): void
    {
        self::assertFalse($this->handler->hasStrategy('unknownType'));
    }

    public function testGetStrategyReturnsRegisteredStrategy(): void
    {
        $strategy = $this->createMockStrategy('testType');
        $this->handler->addStrategy($strategy);

        $result = $this->handler->getStrategy('testType');

        self::assertSame($strategy, $result);
    }

    public function testGetStrategyReturnsNullForUnknownType(): void
    {
        $result = $this->handler->getStrategy('unknownType');

        self::assertNull($result);
    }

    public function testFetchAllCallsAllStrategies(): void
    {
        $editorial = $this->createMock(Editorial::class);
        $section = $this->createMock(Section::class);

        $strategy1 = $this->createMockStrategy('type1');
        $strategy1->expects(self::once())
            ->method('fetch')
            ->with($editorial, $section)
            ->willReturn(['content1' => ['data' => 'value1']]);

        $strategy2 = $this->createMockStrategy('type2');
        $strategy2->expects(self::once())
            ->method('fetch')
            ->with($editorial, $section)
            ->willReturn(['content2' => ['data' => 'value2']]);

        $this->handler->addStrategy($strategy1);
        $this->handler->addStrategy($strategy2);

        $result = $this->handler->fetchAll($editorial, $section);

        self::assertArrayHasKey('type1', $result);
        self::assertArrayHasKey('type2', $result);
        self::assertSame(['content1' => ['data' => 'value1']], $result['type1']);
        self::assertSame(['content2' => ['data' => 'value2']], $result['type2']);
    }

    public function testFetchByTypeReturnsContentFromSpecificStrategy(): void
    {
        $editorial = $this->createMock(Editorial::class);
        $section = $this->createMock(Section::class);
        $expectedContent = ['content' => ['data' => 'value']];

        $strategy = $this->createMockStrategy('targetType');
        $strategy->method('fetch')
            ->with($editorial, $section)
            ->willReturn($expectedContent);

        $this->handler->addStrategy($strategy);

        $result = $this->handler->fetchByType('targetType', $editorial, $section);

        self::assertSame($expectedContent, $result);
    }

    public function testFetchByTypeReturnsEmptyArrayForUnknownType(): void
    {
        $editorial = $this->createMock(Editorial::class);
        $section = $this->createMock(Section::class);

        $result = $this->handler->fetchByType('unknownType', $editorial, $section);

        self::assertSame([], $result);
    }

    public function testGetCombinedResolveDataMergesFromAllStrategies(): void
    {
        $strategy1 = $this->createMockStrategy('type1');
        $strategy1->method('getResolveData')
            ->willReturn([
                'multimedia' => ['media1'],
                'multimediaOpening' => ['opening1'],
            ]);

        $strategy2 = $this->createMockStrategy('type2');
        $strategy2->method('getResolveData')
            ->willReturn([
                'multimedia' => ['media2'],
                'multimediaOpening' => ['opening2'],
            ]);

        $this->handler->addStrategy($strategy1);
        $this->handler->addStrategy($strategy2);

        $result = $this->handler->getCombinedResolveData();

        self::assertContains('media1', $result['multimedia']);
        self::assertContains('media2', $result['multimedia']);
        self::assertContains('opening1', $result['multimediaOpening']);
        self::assertContains('opening2', $result['multimediaOpening']);
    }

    public function testConstructorAcceptsIterableOfStrategies(): void
    {
        $strategy1 = $this->createMockStrategy('type1');
        $strategy2 = $this->createMockStrategy('type2');

        $handler = new RelatedContentHandler([$strategy1, $strategy2]);

        self::assertTrue($handler->hasStrategy('type1'));
        self::assertTrue($handler->hasStrategy('type2'));
    }

    /**
     * @return RelatedContentStrategyInterface&MockObject
     */
    private function createMockStrategy(string $type): RelatedContentStrategyInterface
    {
        $strategy = $this->createMock(RelatedContentStrategyInterface::class);
        $strategy->method('getType')->willReturn($type);
        $strategy->method('supports')->willReturnCallback(fn ($t) => $t === $type);
        $strategy->method('getResolveData')->willReturn(['multimedia' => [], 'multimediaOpening' => []]);

        return $strategy;
    }
}
