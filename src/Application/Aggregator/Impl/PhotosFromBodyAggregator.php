<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use Ec\Editorial\Domain\Model\Body\BodyTagMembershipCard;
use Ec\Editorial\Domain\Model\Body\BodyTagPicture;
use Ec\Multimedia\Infrastructure\Client\Http\QueryMultimediaClient;
use Psr\Log\LoggerInterface;

/**
 * Aggregates photos referenced in body tags.
 * Extracts photos from BodyTagPicture and BodyTagMembershipCard elements.
 */
final class PhotosFromBodyAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryMultimediaClient $queryMultimediaClient,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'photosFromBody';
    }

    public function getPriority(): int
    {
        return 80;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorial();
        $body = $editorial->body();

        $photoIds = $this->extractPhotoIds($body);

        return ['photoIds' => $photoIds];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        $photoIds = $pendingData['photoIds'] ?? [];

        if (empty($photoIds)) {
            return ['photoFromBodyTags' => []];
        }

        $photos = [];
        foreach ($photoIds as $photoId) {
            try {
                $photo = $this->queryMultimediaClient->findPhotoById($photoId);
                $photos[$photoId] = $photo;
            } catch (\Throwable $e) {
                $this->logger->warning('Failed to fetch photo from body', [
                    'photoId' => $photoId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['photoFromBodyTags' => $photos];
    }

    /**
     * Extract all photo IDs from body elements.
     *
     * @return array<string>
     */
    private function extractPhotoIds(\Ec\Editorial\Domain\Model\Body\Body $body): array
    {
        $photoIds = [];

        /** @var BodyTagPicture[] $pictures */
        $pictures = $body->bodyElementsOf(BodyTagPicture::class);
        foreach ($pictures as $picture) {
            $photoIds[] = $picture->id()->id();
        }

        /** @var BodyTagMembershipCard[] $membershipCards */
        $membershipCards = $body->bodyElementsOf(BodyTagMembershipCard::class);
        foreach ($membershipCards as $membershipCard) {
            $photoIds[] = $membershipCard->bodyTagPictureMembership()->id()->id();
        }

        return array_unique($photoIds);
    }
}
