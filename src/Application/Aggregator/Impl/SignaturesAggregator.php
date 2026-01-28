<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Application\Aggregator\AggregatorPriority;
use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use App\Application\Editorial\EditorialTypeConstants;
use Ec\Editorial\Domain\Model\Signature;
use Ec\Journalist\Domain\Model\Journalist;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Psr\Log\LoggerInterface;

/**
 * Aggregates signature (author) data for an editorial.
 */
final class SignaturesAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryJournalistClient $queryJournalistClient,
        private readonly JournalistFactory $journalistFactory,
        private readonly JournalistsDataTransformer $journalistsDataTransformer,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'signatures';
    }

    public function getPriority(): int
    {
        return AggregatorPriority::HIGH;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorial();

        return [
            'signatures' => $editorial->signatures()->getArrayCopy(),
            'hasTwitter' => EditorialTypeConstants::shouldIncludeTwitter($editorial->editorialType()),
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        /** @var Signature[] $signatures */
        $signatures = $pendingData['signatures'] ?? [];
        $hasTwitter = $pendingData['hasTwitter'] ?? false;
        $section = $context->getSection();

        if (empty($signatures)) {
            return ['signatures' => []];
        }

        $result = [];
        foreach ($signatures as $signature) {
            $aliasId = $signature->id()->id();
            $transformed = $this->fetchSignature($aliasId, $section, $hasTwitter);

            if (!empty($transformed)) {
                $result[] = $transformed;
            }
        }

        return ['signatures' => $result];
    }

    /**
     * Fetch and transform a single signature.
     *
     * @return array<string, mixed>
     */
    private function fetchSignature(
        string $aliasId,
        \Ec\Section\Domain\Model\Section $section,
        bool $hasTwitter
    ): array {
        try {
            $aliasIdModel = $this->journalistFactory->buildAliasId($aliasId);

            /** @var Journalist $journalist */
            $journalist = $this->queryJournalistClient->findJournalistByAliasId($aliasIdModel);

            return $this->journalistsDataTransformer
                ->write($aliasId, $journalist, $section, $hasTwitter)
                ->read();
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to fetch signature', [
                'aliasId' => $aliasId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
