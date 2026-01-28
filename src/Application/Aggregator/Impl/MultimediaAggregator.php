<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\Multimedia\MultimediaPhoto;
use Ec\Editorial\Domain\Model\Multimedia\MultimediaVideo;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use Ec\Multimedia\Domain\Model\Multimedia\Multimedia as AbstractMultimedia;
use Ec\Multimedia\Infrastructure\Client\Http\QueryMultimediaClient;
use GuzzleHttp\Promise\Utils;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;

/**
 * Aggregates multimedia data for an editorial.
 * Handles async fetching of multimedia content.
 */
final class MultimediaAggregator extends AbstractAsyncAggregator
{
    private const ASYNC = true;
    private const UNWRAPPED = true;

    public function __construct(
        private readonly QueryMultimediaClient $queryMultimediaClient,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'multimedia';
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorial();
        $multimedia = $editorial->multimedia();

        $multimediaId = $this->getMultimediaId($multimedia);

        if (null === $multimediaId) {
            return ['promises' => [], 'isWidget' => false];
        }

        // Widgets are handled differently (no async fetch)
        if ($multimedia instanceof Widget) {
            return ['promises' => [], 'isWidget' => true, 'multimedia' => $multimedia];
        }

        $promise = $this->queryMultimediaClient->findMultimediaById($multimediaId, self::ASYNC);

        return [
            'promises' => [$promise],
            'isWidget' => false,
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $promises = $pendingData['promises'] ?? [];
        $isWidget = $pendingData['isWidget'] ?? false;

        if ($isWidget) {
            return ['multimedia' => [], 'isWidget' => true];
        }

        if (empty($promises)) {
            return ['multimedia' => []];
        }

        $multimedia = $this->resolveMultimediaPromises($promises);

        return ['multimedia' => $multimedia];
    }

    /**
     * Get the multimedia ID based on type.
     */
    private function getMultimediaId(Multimedia $multimedia): ?string
    {
        if ($multimedia instanceof MultimediaPhoto || $multimedia instanceof MultimediaVideo) {
            $id = $multimedia->id()->id();

            return !empty($id) ? $id : null;
        }

        if ($multimedia instanceof Widget) {
            return $multimedia->id()->id();
        }

        return null;
    }

    /**
     * Resolve multimedia promises and extract results.
     *
     * @param array<Promise> $promises
     *
     * @return array<string, AbstractMultimedia>
     */
    private function resolveMultimediaPromises(array $promises): array
    {
        try {
            return Utils::settle($promises)
                ->then(fn (array $settled) => $this->extractFulfilledMultimedia($settled))
                ->wait(self::UNWRAPPED);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to resolve multimedia promises', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract fulfilled multimedia from settled promises.
     *
     * @param array<string, array{state: string, value?: AbstractMultimedia, reason?: \Throwable}> $settled
     *
     * @return array<string, AbstractMultimedia>
     */
    private function extractFulfilledMultimedia(array $settled): array
    {
        $result = [];

        foreach ($settled as $promise) {
            if (Promise::FULFILLED === $promise['state'] && isset($promise['value'])) {
                /** @var AbstractMultimedia $multimedia */
                $multimedia = $promise['value'];
                $result[$multimedia->id()] = $multimedia;
            }
        }

        return $result;
    }
}
