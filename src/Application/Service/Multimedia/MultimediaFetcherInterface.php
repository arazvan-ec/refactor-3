<?php

declare(strict_types=1);

namespace App\Application\Service\Multimedia;

use Ec\Editorial\Domain\Model\Body\Body;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\NewsBase;

/**
 * Interface for fetching multimedia content asynchronously.
 */
interface MultimediaFetcherInterface
{
    /**
     * Fetch async multimedia for an editorial's main multimedia.
     *
     * @param Multimedia $multimedia The multimedia to fetch
     *
     * @return array<int, mixed> Array of promises
     */
    public function fetchAsync(Multimedia $multimedia): array;

    /**
     * Fetch opening multimedia for an editorial.
     *
     * @param NewsBase $editorial The editorial
     *
     * @return array<string, mixed> Opening multimedia data
     */
    public function fetchOpening(NewsBase $editorial): array;

    /**
     * Fetch photos from body tags.
     *
     * @param Body $body The body content
     *
     * @return array<string, mixed> Map of photo ID to photo data
     */
    public function fetchPhotosFromBodyTags(Body $body): array;

    /**
     * Resolve multimedia promises.
     *
     * @param array<int, mixed> $promises Array of promises
     *
     * @return array<string, mixed> Resolved multimedia data
     */
    public function resolvePromises(array $promises): array;
}
