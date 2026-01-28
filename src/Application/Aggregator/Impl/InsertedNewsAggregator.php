<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Application\Aggregator\Trait\SignatureFetcherTrait;
use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use Ec\Editorial\Domain\Model\Body\BodyTagInsertedNews;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Ec\Section\Domain\Model\QuerySectionClient;
use Ec\Section\Domain\Model\Section;
use Psr\Log\LoggerInterface;

/**
 * Aggregates inserted news data from body elements.
 * Fetches related editorials embedded in the body.
 */
final class InsertedNewsAggregator extends AbstractAsyncAggregator
{
    use SignatureFetcherTrait;

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
        return 'insertedNews';
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
        $editorial = $context->getEditorial();
        $body = $editorial->body();

        /** @var BodyTagInsertedNews[] $insertedNews */
        $insertedNews = $body->bodyElementsOf(BodyTagInsertedNews::class);

        $editorialIds = [];
        foreach ($insertedNews as $inserted) {
            $editorialIds[] = $inserted->editorialId()->id();
        }

        return ['editorialIds' => $editorialIds];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $editorialIds = $pendingData['editorialIds'] ?? [];

        if (empty($editorialIds)) {
            return ['insertedNews' => []];
        }

        $result = [];
        foreach ($editorialIds as $editorialId) {
            $data = $this->fetchInsertedEditorial($editorialId);
            if (null !== $data) {
                $result[$editorialId] = $data;
            }
        }

        // Store in context for body transformer
        $context->setSharedData('insertedNews', $result);

        return ['insertedNews' => $result];
    }

    // SignatureFetcherTrait abstract methods
    protected function getQueryJournalistClient(): QueryJournalistClient
    {
        return $this->queryJournalistClient;
    }

    protected function getJournalistFactory(): JournalistFactory
    {
        return $this->journalistFactory;
    }

    protected function getJournalistsDataTransformer(): JournalistsDataTransformer
    {
        return $this->journalistsDataTransformer;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getTraitLogContext(): string
    {
        return 'insertedNews';
    }

    /**
     * Fetch and process a single inserted editorial.
     *
     * @return array<string, mixed>|null
     */
    private function fetchInsertedEditorial(string $editorialId): ?array
    {
        try {
            /** @var Editorial $editorial */
            $editorial = $this->queryEditorialClient->findEditorialById($editorialId);

            if (!$editorial->isVisible()) {
                return null;
            }

            /** @var Section $section */
            $section = $this->querySectionClient->findSectionById($editorial->sectionId());

            $signatures = $this->fetchSignaturesForEditorial($editorial, $section);
            $multimediaId = $this->getMultimediaId($editorial);

            return [
                'editorial' => $editorial,
                'section' => $section,
                'signatures' => $signatures,
                'multimediaId' => $multimediaId,
            ];
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to fetch inserted editorial', [
                'editorialId' => $editorialId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get multimedia ID from editorial, with fallback to metaImage.
     */
    private function getMultimediaId(Editorial $editorial): string
    {
        $multimediaId = $editorial->multimedia()->id()->id();

        return !empty($multimediaId) ? $multimediaId : ($editorial->metaImage() ?? '');
    }
}
