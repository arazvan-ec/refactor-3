<?php

declare(strict_types=1);

namespace App\Application\Service\Editorial;

use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use Ec\Editorial\Domain\Model\Signature;
use Ec\Editorial\Domain\Model\Signatures;
use Ec\Journalist\Domain\Model\Journalist;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Ec\Section\Domain\Model\Section;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for fetching and transforming journalist signatures.
 * Extracted from EditorialOrchestrator to comply with Single Responsibility Principle.
 */
final class SignatureFetcher implements SignatureFetcherInterface
{
    public function __construct(
        private readonly QueryJournalistClient $queryJournalistClient,
        private readonly JournalistFactory $journalistFactory,
        private readonly JournalistsDataTransformer $journalistsDataTransformer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetch(Signatures $signatures, Section $section, bool $hasTwitter = false): array
    {
        $result = [];

        /** @var Signature $signature */
        foreach ($signatures->getArrayCopy() as $signature) {
            $signatureData = $this->fetchByAliasId(
                $signature->id()->id(),
                $section,
                $hasTwitter
            );

            if (!empty($signatureData)) {
                $result[] = $signatureData;
            }
        }

        return $result;
    }

    public function fetchByAliasId(string $aliasId, Section $section, bool $hasTwitter = false): array
    {
        try {
            $aliasIdModel = $this->journalistFactory->buildAliasId($aliasId);

            /** @var Journalist $journalist */
            $journalist = $this->queryJournalistClient->findJournalistByAliasId($aliasIdModel);

            return $this->journalistsDataTransformer
                ->write($aliasId, $journalist, $section, $hasTwitter)
                ->read();
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to fetch journalist signature', [
                'aliasId' => $aliasId,
                'error' => $throwable->getMessage(),
            ]);

            return [];
        }
    }
}
