<?php

declare(strict_types=1);

namespace App\Application\Service\Multimedia;

use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\Multimedia\MultimediaId;
use Ec\Multimedia\Domain\Model\Multimedia as MultimediaModel;

/**
 * Interface for multimedia processing operations.
 * Replaces MultimediaTrait with proper dependency injection.
 */
interface MultimediaProcessorInterface
{
    /**
     * Get the multimedia ID from a multimedia object.
     *
     * @param Multimedia $multimedia The multimedia object
     *
     * @return MultimediaId|null The multimedia ID or null if not applicable
     */
    public function getMultimediaId(Multimedia $multimedia): ?MultimediaId;

    /**
     * Generate landscape shots for multimedia.
     *
     * @param MultimediaModel $multimedia The multimedia model
     *
     * @return array<string, string> Map of size type to URL
     */
    public function getShotsLandscape(MultimediaModel $multimedia): array;

    /**
     * Generate landscape shots from media array.
     *
     * @param array{opening: MultimediaModel\MultimediaPhoto, resource: \Ec\Multimedia\Domain\Model\Photo\Photo} $multimediaOpening
     *
     * @return array<string, string> Map of size type to URL
     */
    public function getShotsLandscapeFromMedia(array $multimediaOpening): array;

    /**
     * Get available size configurations.
     *
     * @return array<string, array<string, string>> Map of size type to dimensions
     */
    public function getSizes(): array;
}
