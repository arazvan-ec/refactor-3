<?php

declare(strict_types=1);

namespace App\Application\Strategy\RelatedContent;

use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\EditorialId;
use Ec\Section\Domain\Model\Section;

/**
 * Strategy for fetching recommended editorials.
 * Implements Strategy Pattern for Open/Closed Principle compliance.
 */
final class RecommendedEditorialsStrategy extends AbstractRelatedContentStrategy
{
    private const TYPE = 'recommendedEditorials';

    /** @var Editorial[] */
    private array $fetchedEditorials = [];

    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function fetch(Editorial $editorial, Section $section): array
    {
        $result = [];
        $this->fetchedEditorials = [];

        $recommendedEditorials = $editorial->recommendedEditorials();

        /** @var EditorialId $recommendedEditorialId */
        foreach ($recommendedEditorials->editorialIds() as $recommendedEditorialId) {
            $editorialId = $recommendedEditorialId->id();
            $data = $this->processRelatedEditorial($editorialId, $section);

            if (null !== $data) {
                $result[$editorialId] = $data;
                $this->fetchedEditorials[] = $data['editorial'];
            }
        }

        // Resolve multimedia promises after all editorials processed
        $this->resolveData['multimedia'] = $this->resolveMultimediaPromises();

        return $result;
    }

    /**
     * Get the list of fetched editorials for transformation.
     *
     * @return Editorial[]
     */
    public function getFetchedEditorials(): array
    {
        return $this->fetchedEditorials;
    }
}
