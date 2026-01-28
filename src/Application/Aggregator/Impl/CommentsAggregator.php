<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Ec\Snaapi\Infrastructure\Client\Http\QueryLegacyClient;
use Psr\Log\LoggerInterface;

/**
 * Aggregates comment count for an editorial.
 */
final class CommentsAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryLegacyClient $queryLegacyClient,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'comments';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function aggregate(AggregationContext $context): array
    {
        return [
            'editorialId' => $context->getEditorialId(),
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $editorialId = $pendingData['editorialId'];

        try {
            /** @var array{options: array{totalrecords?: int}} $comments */
            $comments = $this->queryLegacyClient->findCommentsByEditorialId($editorialId);
            $count = $comments['options']['totalrecords'] ?? 0;
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to fetch comments', [
                'editorialId' => $editorialId,
                'error' => $e->getMessage(),
            ]);
            $count = 0;
        }

        return ['countComments' => $count];
    }
}
