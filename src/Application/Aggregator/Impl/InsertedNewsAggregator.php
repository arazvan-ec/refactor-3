<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use Ec\Editorial\Domain\Model\Body\BodyTagInsertedNews;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Editorial\Domain\Model\Signature;
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

            $signatures = $this->fetchSignatures($editorial, $section);
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
            $this->logger->warning('Failed to fetch signature for inserted news', [
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
