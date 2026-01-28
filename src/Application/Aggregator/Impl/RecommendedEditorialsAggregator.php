<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Editorial\Domain\Model\Signature;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Ec\Section\Domain\Model\QuerySectionClient;
use Ec\Section\Domain\Model\Section;
use Psr\Log\LoggerInterface;

/**
 * Aggregates recommended editorials data.
 * Fetches related editorials configured in the editorial.
 */
final class RecommendedEditorialsAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryEditorialClient $queryEditorialClient,
        private readonly QuerySectionClient $querySectionClient,
        private readonly QueryJournalistClient $queryJournalistClient,
        private readonly JournalistFactory $journalistFactory,
        private readonly JournalistsDataTransformer $journalistsDataTransformer,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'recommendedEditorials';
    }

    public function getPriority(): int
    {
        return 70;
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return ['signatures'];
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorialAsEditorial();
        $recommendedEditorials = $editorial->recommendedEditorials();

        $editorialIds = [];
        /** @var EditorialId $editorialId */
        foreach ($recommendedEditorials->editorialIds() as $editorialId) {
            $editorialIds[] = $editorialId->id();
        }

        return ['editorialIds' => $editorialIds];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $editorialIds = $pendingData['editorialIds'] ?? [];

        if (empty($editorialIds)) {
            return [
                'recommendedEditorials' => [],
                'recommendedNews' => [],
            ];
        }

        $result = [];
        $recommendedNews = [];

        foreach ($editorialIds as $editorialId) {
            $data = $this->fetchRecommendedEditorial($editorialId);
            if (null !== $data) {
                $result[$editorialId] = $data;
                $recommendedNews[] = $data['editorial'];
            }
        }

        // Store in context for transformer
        $context->setSharedData('recommendedEditorials', $result);
        $context->setSharedData('recommendedNews', $recommendedNews);

        return [
            'recommendedEditorials' => $result,
            'recommendedNews' => $recommendedNews,
        ];
    }

    /**
     * Fetch and process a single recommended editorial.
     *
     * @return array<string, mixed>|null
     */
    private function fetchRecommendedEditorial(string $editorialId): ?array
    {
        try {
            /** @var Editorial $editorial */
            $editorial = $this->queryEditorialClient->findEditorialById($editorialId);

            if (!$editorial->isVisible()) {
                return null;
            }

            /** @var Section $section */
            $section = $this->querySectionClient->findSectionById($editorial->sectionId());

            $signatures = $this->fetchSignatures($editorial, $section);
            $multimediaId = $this->getMultimediaId($editorial);

            return [
                'editorial' => $editorial,
                'section' => $section,
                'signatures' => $signatures,
                'multimediaId' => $multimediaId,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Failed to fetch recommended editorial', [
                'editorialId' => $editorialId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch signatures for an editorial.
     *
     * @return array<array<string, mixed>>
     */
    private function fetchSignatures(Editorial $editorial, Section $section): array
    {
        $result = [];

        /** @var Signature $signature */
        foreach ($editorial->signatures()->getArrayCopy() as $signature) {
            $transformed = $this->fetchSignature($signature->id()->id(), $section);
            if (!empty($transformed)) {
                $result[] = $transformed;
            }
        }

        return $result;
    }

    /**
     * Fetch and transform a single signature.
     *
     * @return array<string, mixed>
     */
    private function fetchSignature(string $aliasId, Section $section): array
    {
        try {
            $aliasIdModel = $this->journalistFactory->buildAliasId($aliasId);
            $journalist = $this->queryJournalistClient->findJournalistByAliasId($aliasIdModel);

            return $this->journalistsDataTransformer
                ->write($aliasId, $journalist, $section, false)
                ->read();
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to fetch signature for recommended editorial', [
                'aliasId' => $aliasId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get multimedia ID from editorial.
     */
    private function getMultimediaId(Editorial $editorial): string
    {
        $multimediaId = $editorial->multimedia()->id()->id();

        if (!empty($multimediaId)) {
            return $multimediaId;
        }

        return $editorial->metaImage() ?? '';
    }
}
