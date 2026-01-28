<?php

declare(strict_types=1);

namespace App\Tests\Application\Service\Editorial;

use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use App\Application\Service\Editorial\SignatureFetcher;
use Ec\Editorial\Domain\Model\Signature;
use Ec\Editorial\Domain\Model\SignatureId;
use Ec\Editorial\Domain\Model\Signatures;
use Ec\Journalist\Domain\Model\AliasId;
use Ec\Journalist\Domain\Model\Journalist;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Ec\Section\Domain\Model\Section;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(SignatureFetcher::class)]
final class SignatureFetcherTest extends TestCase
{
    private QueryJournalistClient&MockObject $queryJournalistClient;
    private JournalistFactory&MockObject $journalistFactory;
    private JournalistsDataTransformer&MockObject $journalistsDataTransformer;
    private LoggerInterface&MockObject $logger;
    private SignatureFetcher $signatureFetcher;

    protected function setUp(): void
    {
        $this->queryJournalistClient = $this->createMock(QueryJournalistClient::class);
        $this->journalistFactory = $this->createMock(JournalistFactory::class);
        $this->journalistsDataTransformer = $this->createMock(JournalistsDataTransformer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->signatureFetcher = new SignatureFetcher(
            $this->queryJournalistClient,
            $this->journalistFactory,
            $this->journalistsDataTransformer,
            $this->logger
        );
    }

    public function testFetchReturnsEmptyArrayWhenNoSignatures(): void
    {
        $signatures = $this->createMock(Signatures::class);
        $signatures->method('getArrayCopy')->willReturn([]);
        $section = $this->createMock(Section::class);

        $result = $this->signatureFetcher->fetch($signatures, $section);

        self::assertSame([], $result);
    }

    public function testFetchReturnsTransformedSignatures(): void
    {
        $aliasId = 'journalist-123';
        $expectedData = ['id' => $aliasId, 'name' => 'John Doe'];

        $signatureId = $this->createMock(SignatureId::class);
        $signatureId->method('id')->willReturn($aliasId);

        $signature = $this->createMock(Signature::class);
        $signature->method('id')->willReturn($signatureId);

        $signatures = $this->createMock(Signatures::class);
        $signatures->method('getArrayCopy')->willReturn([$signature]);

        $section = $this->createMock(Section::class);

        $aliasIdModel = $this->createMock(AliasId::class);
        $this->journalistFactory->method('buildAliasId')
            ->with($aliasId)
            ->willReturn($aliasIdModel);

        $journalist = $this->createMock(Journalist::class);
        $this->queryJournalistClient->method('findJournalistByAliasId')
            ->with($aliasIdModel)
            ->willReturn($journalist);

        $this->journalistsDataTransformer->method('write')
            ->with($aliasId, $journalist, $section, false)
            ->willReturnSelf();
        $this->journalistsDataTransformer->method('read')
            ->willReturn($expectedData);

        $result = $this->signatureFetcher->fetch($signatures, $section);

        self::assertCount(1, $result);
        self::assertSame($expectedData, $result[0]);
    }

    public function testFetchByAliasIdReturnsEmptyArrayOnException(): void
    {
        $aliasId = 'journalist-123';
        $section = $this->createMock(Section::class);

        $this->journalistFactory->method('buildAliasId')
            ->willThrowException(new \RuntimeException('Not found'));

        $this->logger->expects(self::once())
            ->method('error')
            ->with('Failed to fetch journalist signature', self::anything());

        $result = $this->signatureFetcher->fetchByAliasId($aliasId, $section);

        self::assertSame([], $result);
    }

    public function testFetchWithTwitterFlagPassesToTransformer(): void
    {
        $aliasId = 'journalist-123';
        $expectedData = ['id' => $aliasId, 'twitter' => '@johndoe'];

        $signatureId = $this->createMock(SignatureId::class);
        $signatureId->method('id')->willReturn($aliasId);

        $signature = $this->createMock(Signature::class);
        $signature->method('id')->willReturn($signatureId);

        $signatures = $this->createMock(Signatures::class);
        $signatures->method('getArrayCopy')->willReturn([$signature]);

        $section = $this->createMock(Section::class);

        $aliasIdModel = $this->createMock(AliasId::class);
        $this->journalistFactory->method('buildAliasId')->willReturn($aliasIdModel);

        $journalist = $this->createMock(Journalist::class);
        $this->queryJournalistClient->method('findJournalistByAliasId')->willReturn($journalist);

        $this->journalistsDataTransformer->expects(self::once())
            ->method('write')
            ->with($aliasId, $journalist, $section, true)
            ->willReturnSelf();
        $this->journalistsDataTransformer->method('read')->willReturn($expectedData);

        $result = $this->signatureFetcher->fetch($signatures, $section, true);

        self::assertSame([$expectedData], $result);
    }
}
