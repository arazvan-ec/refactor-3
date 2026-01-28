<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use Ec\Tag\Domain\Model\QueryTagClient;
use Ec\Tag\Domain\Model\Tag;
use Psr\Log\LoggerInterface;

/**
 * Aggregates tag data for an editorial.
 * Fetches all tags associated with the editorial.
 */
final class TagsAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryTagClient $queryTagClient,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'tags';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorial();
        $tagIds = $editorial->tags()->getArrayCopy();

        return [
            'tagIds' => $tagIds,
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $tagIds = $pendingData['tagIds'] ?? [];

        if (empty($tagIds)) {
            return ['tags' => []];
        }

        $tags = [];
        foreach ($tagIds as $tag) {
            try {
                /** @var Tag $fetchedTag */
                $fetchedTag = $this->queryTagClient->findTagById($tag->id());
                $tags[] = $fetchedTag;
            } catch (\Throwable $e) {
                $this->logger->warning('Failed to fetch tag', [
                    'tagId' => $tag->id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['tags' => $tags];
    }
}
