<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Orchestrator\Chain\Multimedia\MultimediaOrchestratorHandler;
use App\Orchestrator\Exceptions\OrchestratorTypeNotExistException;
use Ec\Infrastructure\Client\Exceptions\InvalidBodyException;
use Ec\Multimedia\Domain\Model\Multimedia\Multimedia as AbstractMultimedia;
use Ec\Multimedia\Infrastructure\Client\Http\Media\QueryMultimediaClient as QueryMultimediaOpeningClient;
use Psr\Log\LoggerInterface;

/**
 * Aggregates opening multimedia data for an editorial.
 */
final class MultimediaOpeningAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryMultimediaOpeningClient $queryMultimediaOpeningClient,
        private readonly MultimediaOrchestratorHandler $multimediaOrchestratorHandler,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'multimediaOpening';
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorialAsEditorial();
        $opening = $editorial->opening();

        return [
            'multimediaId' => $opening->multimediaId(),
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $multimediaId = $pendingData['multimediaId'] ?? '';

        if (empty($multimediaId)) {
            return ['multimediaOpening' => []];
        }

        try {
            /** @var AbstractMultimedia $multimedia */
            $multimedia = $this->queryMultimediaOpeningClient->findMultimediaById($multimediaId);
            $result = $this->multimediaOrchestratorHandler->handler($multimedia);

            return ['multimediaOpening' => $result];
        } catch (OrchestratorTypeNotExistException|InvalidBodyException $e) {
            $this->logger->warning('Failed to handle opening multimedia', [
                'multimediaId' => $multimediaId,
                'error' => $e->getMessage(),
            ]);

            return ['multimediaOpening' => []];
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error fetching opening multimedia', [
                'multimediaId' => $multimediaId,
                'error' => $e->getMessage(),
            ]);

            return ['multimediaOpening' => []];
        }
    }
}
